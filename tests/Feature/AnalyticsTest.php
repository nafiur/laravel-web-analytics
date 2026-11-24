<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\CountryFlag;
use Nafiur\WebAnalytics\Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_dashboard_reports_zero_live_visitors_and_a_visitor_series(): void
    {
        $response = $this->getJson('/web-analytics/data?range=24h');

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(0, $response->json('analytics.summary.live_visitors'));
        $this->assertArrayHasKey('visitors', $response->json('analytics.time_series'));
    }

    public function test_one_hour_range_excludes_older_records(): void
    {
        LwaLogs::create($this->log(['created_at' => now()]));
        LwaLogs::create($this->log(['created_at' => now()->subHours(3)]));

        $response = $this->getJson('/web-analytics/data?range=1h');

        $response->assertOk();
        $this->assertSame(1, $response->json('analytics.summary.total_requests'));
    }

    public function test_missing_hours_are_zero_filled(): void
    {
        LwaLogs::create($this->log(['created_at' => now()]));

        $response = $this->getJson('/web-analytics/data?range=24h');

        $this->assertGreaterThanOrEqual(24, count($response->json('analytics.time_series.labels')));
    }

    public function test_explicit_tenant_id_and_header_are_ignored_by_default(): void
    {
        config()->set('laravel-web-analytics.multi_tenancy.enabled', true);
        config()->set('laravel-web-analytics.multi_tenancy.tenant_resolver', fn () => 'tenant-a');

        LwaLogs::create($this->log(['tenant_id' => 'tenant-a']));
        LwaLogs::create($this->log(['tenant_id' => 'tenant-b']));

        $response = $this->withHeader('X-Tenant-ID', 'tenant-b')
            ->getJson('/web-analytics/data?tenant_id=tenant-b');

        $response->assertOk();
        $this->assertSame(1, $response->json('analytics.summary.total_requests'));
        $this->assertSame('tenant-a', $response->json('analytics.summary.tenant_id'));
    }

    public function test_authorized_tenant_override_is_honored(): void
    {
        config()->set('laravel-web-analytics.multi_tenancy.enabled', true);
        config()->set('laravel-web-analytics.multi_tenancy.tenant_resolver', fn () => 'tenant-a');
        config()->set('laravel-web-analytics.multi_tenancy.tenant_override_authorizer',
            fn (string $requested) => $requested === 'tenant-b');

        LwaLogs::create($this->log(['tenant_id' => 'tenant-a']));
        LwaLogs::create($this->log(['tenant_id' => 'tenant-b']));

        $response = $this->getJson('/web-analytics/data?tenant_id=tenant-b');

        $response->assertOk();
        $this->assertSame(1, $response->json('analytics.summary.total_requests'));
        $this->assertSame('tenant-b', $response->json('analytics.summary.tenant_id'));
    }

    public function test_export_neutralizes_csv_formula_injection(): void
    {
        LwaLogs::create($this->log(['path' => '=SUM(A1:A9)']));

        $response = $this->get('/web-analytics/export?range=all');

        $this->assertStringContainsString('charset=UTF-8', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString("'=SUM(A1:A9)", $response->streamedContent());
    }

    public function test_stream_response_is_short_lived_and_paced_by_eventsource(): void
    {
        LwaLogs::create($this->log(['path' => '/stream-check']));

        $started = microtime(true);
        $content = $this->get('/web-analytics/stream?last_id=0')->streamedContent();
        $elapsed = microtime(true) - $started;

        $this->assertLessThan(2, $elapsed);
        $this->assertStringContainsString('retry: ', $content);
        $this->assertStringContainsString('event: stats', $content);
        $this->assertStringContainsString('id: ', $content);
    }

    public function test_country_breakdown_includes_flag_emoji(): void
    {
        LwaLogs::create($this->log(['country' => 'Bangladesh']));
        LwaLogs::create($this->log(['country' => 'Unknown']));

        $countries = collect($this->getJson('/web-analytics/data?range=all')
            ->json('analytics.breakdowns.top_countries'));

        $bd = $countries->firstWhere('name', 'Bangladesh');
        $unknown = $countries->firstWhere('name', 'Unknown');

        $this->assertSame('BD', CountryFlag::code('Bangladesh'));
        $this->assertSame(CountryFlag::emoji('Bangladesh'), $bd['flag']);
        $this->assertNotSame('', $bd['flag']);
        $this->assertSame('', $unknown['flag']);
    }

    public function test_dashboard_has_only_the_requested_sidebar_links(): void
    {
        $content = $this->get('/web-analytics')->getContent();

        $this->assertStringContainsString('href="' . route('lwa_overview') . '"', $content);
        $this->assertStringContainsString('href="' . route('lwa_events') . '"', $content);
        $this->assertSame(13, substr_count($content, 'class="sidebar-link'));

        foreach (['Overview', 'Real-time', 'Visitors', 'Traffic Sources', 'Pages &amp; Screens', 'Geography',
                  'Devices &amp; Browsers', 'Campaigns', 'Events &amp; Goals', 'Conversions',
                  'Performance', 'Error Monitoring', 'Settings'] as $label) {
            $this->assertStringContainsString($label, $content);
        }

        foreach (['AI Crawl Control', 'Web3', 'Manage account', 'Back to Domains'] as $removed) {
            $this->assertStringNotContainsString($removed, $content);
        }
    }

    public function test_overview_exposes_honest_engagement_metrics(): void
    {
        $base = now()->subMinutes(10);
        LwaLogs::create($this->log([
            'session_id' => 's_engaged',
            'ip_address' => '8.8.8.8',
            'path'       => '/a',
            'created_at' => $base,
        ]));
        LwaLogs::create($this->log([
            'session_id' => 's_engaged',
            'ip_address' => '8.8.8.8',
            'path'       => '/b',
            'created_at' => $base->copy()->addMinutes(2),
        ]));
        LwaLogs::create($this->log([
            'session_id' => 's_bounce',
            'ip_address' => '1.1.1.1',
            'path'       => '/c',
            'created_at' => $base,
        ]));

        $summary = $this->getJson('/web-analytics/data?range=all')->json('analytics.summary');

        $this->assertSame(2, $summary['total_visitors']);
        $this->assertSame(2, $summary['sessions']);
        $this->assertSame(3, $summary['pageviews']);
        $this->assertEquals(50.0, $summary['bounce_rate']);
        $this->assertGreaterThan(0, $summary['avg_engagement_time']);

        $content = $this->get('/web-analytics')->getContent();
        foreach (['chartOverviewTraffic', 'Total Visitors', 'Avg Engagement', 'Live Now'] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }
    }

    public function test_every_sidebar_section_has_its_own_route_and_pane(): void
    {
        $sections = [
            'lwa_overview'    => 'chartOverviewTraffic',
            'lwa_realtime'    => 'lwaLiveMap',
            'lwa_visitors'    => 'Top Client IPs',
            'lwa_sources'     => 'Top Referrers',
            'lwa_pages'       => 'Top Paths by Bandwidth',
            'lwa_geography'   => 'lwaGeographyMap',
            'lwa_devices'     => 'Device types over time',
            'lwa_campaigns'   => 'UTM Campaigns',
            'lwa_events'      => 'No events recorded yet',
            'lwa_conversions' => 'Conversions',
            'lwa_performance' => 'Origin response',
            'lwa_errors'      => 'Total Error Requests',
            'lwa_settings'    => 'settings-general',
        ];

        foreach ($sections as $name => $needle) {
            $response = $this->get(route($name));
            $response->assertOk();
            $response->assertSee($needle, false);
        }
    }

    public function test_technology_breakdowns_use_icons_instead_of_country_flags(): void
    {
        LwaLogs::create($this->log([
            'country' => 'Bangladesh',
            'city'    => 'Dhaka',
            'os'      => 'Windows 10/11',
            'browser' => 'Google Chrome',
            'device'  => 'Mobile',
        ]));

        $breakdowns = $this->getJson(route('lwa_data', ['range' => 'all']))
            ->json('analytics.breakdowns');

        $os = collect($breakdowns['top_os'])->firstWhere('name', 'Windows 10/11');
        $browser = collect($breakdowns['top_browsers'])->firstWhere('name', 'Google Chrome');

        $this->assertSame('', $os['flag']);
        $this->assertStringContainsString('<svg', $os['icon']);
        $this->assertSame('', $browser['flag']);
        $this->assertStringContainsString('<svg', $browser['icon']);

        $country = collect($breakdowns['top_countries'])->firstWhere('name', 'Bangladesh');
        $this->assertNotEmpty($country['flag']);

        $content = $this->get(route('lwa_devices'))->getContent();
        $this->assertStringContainsString('<svg viewBox="0 0 24 24"', $content);
    }

    public function test_devices_page_renders_full_technology_dashboard(): void
    {
        LwaLogs::create($this->log([
            'device'     => 'Mobile',
            'os'         => 'Android',
            'browser'    => 'Chrome',
            'user_agent' => 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36',
        ]));
        LwaLogs::create($this->log([
            'device'     => 'Desktop',
            'os'         => 'Windows',
            'browser'    => 'Firefox',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:160.0) Gecko/20100101 Firefox/160.0',
        ]));

        $content = $this->get(route('lwa_devices'))->getContent();

        foreach ([
            'Device types over time',
            'Top operating systems',
            'Top browsers',
            'Top user agents',
            'chartDeviceTypes',
            'lwaTechnologyExportData',
            'Export CSV',
            'Export JSON',
            'Mobile',
            'Android',
            'Firefox',
        ] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }
    }

    public function test_device_series_query_has_one_select_and_group_by(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $this->getJson(route('lwa_data', ['range' => '24h']))->assertOk();

        $deviceQueries = array_values(array_filter(
            $queries,
            fn (string $sql): bool => str_contains($sql, 'device_count')
        ));

        $this->assertCount(1, $deviceQueries);
        $this->assertSame(1, substr_count($deviceQueries[0], 'as bucket_time'));
        $this->assertSame(1, substr_count($deviceQueries[0], 'group by'));
        $this->assertStringNotContainsString('req_count', $deviceQueries[0]);
    }

    public function test_device_over_time_and_user_agents_are_exposed_in_data_payload(): void
    {
        LwaLogs::create($this->log([
            'device'     => 'Mobile',
            'user_agent' => 'MobileTestAgent/1.0',
        ]));

        $analytics = $this->getJson(route('lwa_data', ['range' => '24h']))->json('analytics');

        $series = collect($analytics['time_series']['device_over_time']['series'] ?? []);
        $mobile = $series->firstWhere('name', 'Mobile');

        $this->assertNotNull($mobile);
        $this->assertSame(1, array_sum($mobile['data']));
        $this->assertSame(count($analytics['time_series']['labels']), count($mobile['data']));
        $this->assertSame('MobileTestAgent/1.0', $analytics['breakdowns']['top_user_agents'][0]['name'] ?? null);
    }

    public function test_geography_city_breakdown_is_dynamic(): void
    {
        LwaLogs::create($this->log([
            'country' => 'BD',
            'city'    => 'Dhaka',
            'path'    => '/city-check',
        ]));

        $content = $this->get(route('lwa_geography'))->getContent();

        $this->assertStringContainsString('Dhaka', $content);
        $this->assertStringNotContainsString('City-level reporting requires GeoIP data.', $content);

        $cities = $this->getJson(route('lwa_data', ['range' => 'all']))->json('analytics.breakdowns.top_cities');
        $this->assertSame('Dhaka', $cities[0]['name']);
    }

    public function test_country_breakdowns_render_flags_on_all_report_pages(): void
    {
        LwaLogs::create($this->log(['country' => 'BD', 'path' => '/country-flag']));

        foreach (['lwa_overview', 'lwa_visitors', 'lwa_geography', 'lwa_sources', 'lwa_settings'] as $route) {
            $content = $this->get(route($route))->getContent();
            $this->assertStringContainsString('flagcdn.com/w20/bd.png', $content, "Missing flag on {$route}");
            $this->assertStringContainsString('🇧🇩', $content, "Missing emoji fallback on {$route}");
        }
    }

    public function test_live_feed_shows_country_flags(): void
    {
        LwaLogs::create($this->log([
            'country' => 'BD',
            'path'    => '/flag-check',
        ]));

        $content = $this->get(route('lwa_realtime'))->getContent();

        $this->assertStringContainsString('Country', $content);
        $this->assertStringContainsString('🇧🇩', $content);
        $this->assertStringContainsString('/flag-check', $content);
    }

    public function test_settings_page_renders_all_sections(): void
    {
        $content = $this->get(route('lwa_settings'))->getContent();

        foreach ([
            'settings-general', 'settings-dashboard', 'settings-collection',
            'settings-maintenance', 'settings-about', 'settingLanguage',
            'settingAppearance', 'settingTimezone', '/web-analytics/settings/prune',
            '/web-analytics/settings/empty', 'value="hi"', 'Asia/Tokyo',
            'id="languageFlag"', '🇺🇸',
        ] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }

        $this->assertStringNotContainsString('Display name', $content);
        $this->assertStringContainsString('🇧🇩 বাংলা', $content);
        $this->assertStringNotContainsString('ðŸ', $content);
        $this->assertStringContainsString('optgroup label="Africa"', $content);
        $this->assertStringContainsString('value="Pacific/Kiritimati"', $content);
        $this->assertGreaterThan(400, substr_count($content, '<option value="'));
    }

    public function test_time_series_exposes_timestamps_and_granularity(): void
    {
        LwaLogs::create($this->log(['created_at' => now()]));

        $series = $this->getJson('/web-analytics/data?range=1h')->json('analytics.time_series');

        $this->assertSame('hour', $series['granularity']);
        $this->assertSame(count($series['labels']), count($series['timestamps']));
        $this->assertNotEmpty($series['timestamps'][0]);
    }

    public function test_dashboard_ships_design_tokens_and_explicit_ui_states(): void
    {
        $content = $this->get('/web-analytics')->getContent();

        foreach ([
            '--lwa-bg:', '[data-theme="dark"]', 'data-lucide="activity"',
            'lwa-skeleton', 'lwaErrorToast',
            'lwaRefreshBtn', 'themeToggle', 'sidebarCollapseToggle',
            'sidebar-label', 'panel-left-close', 'lwa-sidebar-collapsed',
            'family=Inter', 'ApexCharts', 'lucide@latest',
        ] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }

        $overview = $this->get(route('lwa_overview'))->getContent();
        $this->assertStringContainsString('lwaConnectionBadge', $this->get(route('lwa_realtime'))->getContent());
        $this->assertStringContainsString('lwaOverviewMap', $overview);
        $this->assertStringContainsString('data-lwa-map="lwaOverviewMap"', $overview);
    }

    public function test_ip_breakdown_and_stream_include_country_flags(): void
    {
        LwaLogs::create($this->log([
            'ip_address' => '103.230.104.10',
            'country'    => 'Bangladesh',
        ]));
        LwaLogs::create($this->log([
            'ip_address' => '103.230.104.11',
            'country'    => 'Bangladesh',
        ]));

        $ips = $this->getJson('/web-analytics/data?range=all')->json('analytics.breakdowns.top_ips');
        $flag = CountryFlag::emoji('Bangladesh');

        $this->assertSame($flag, $ips[0]['flag']);

        $stream = $this->get('/web-analytics/stream?last_id=1')->streamedContent();
        preg_match("/event: traffic\ndata: (.+)/", $stream, $matches);
        $this->assertNotEmpty($matches, 'Expected a traffic event in the SSE response');
        $payload = json_decode($matches[1], true);
        $this->assertSame($flag, $payload['logs'][0]['flag']);
    }

    public function test_settings_page_includes_alerts_and_export_sections(): void
    {
        $content = $this->get(route('lwa_settings'))->getContent();

        $this->assertStringContainsString('settings-alerts', $content);
        $this->assertStringContainsString('settings-export', $content);
        $this->assertStringContainsString('/web-analytics/settings/test-alert', $content);
    }

    public function test_test_alert_endpoint_sends_when_enabled(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', true);
        config()->set('laravel-web-analytics.alerts.channel', 'slack');
        config()->set('laravel-web-analytics.alerts.webhook_url', 'https://example.test/hook');
        \Illuminate\Support\Facades\Http::fake();

        $this->post('/web-analytics/settings/test-alert')->assertRedirect();

        \Illuminate\Support\Facades\Http::assertSentCount(1);
    }

    public function test_test_alert_endpoint_rejects_when_disabled(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', false);

        $this->post('/web-analytics/settings/test-alert')->assertSessionHasErrors('alert');
    }

    public function test_prune_endpoint_removes_only_old_records(): void
    {
        LwaLogs::create($this->log(['created_at' => now()->subDays(40)]));
        LwaLogs::create($this->log(['created_at' => now()]));

        $this->post('/web-analytics/settings/prune', ['days' => 30])->assertRedirect();

        $this->assertSame(1, LwaLogs::count());
    }

    public function test_empty_table_requires_typed_confirmation(): void
    {
        LwaLogs::create($this->log());

        $this->post('/web-analytics/settings/empty', [])->assertSessionHasErrors('confirm');
        $this->assertSame(1, LwaLogs::count());

        $this->post('/web-analytics/settings/empty', ['confirm' => 'DELETE'])->assertRedirect();
        $this->assertSame(0, LwaLogs::count());
    }

    public function test_empty_table_is_scoped_to_current_tenant(): void
    {
        config()->set('laravel-web-analytics.multi_tenancy.enabled', true);
        config()->set('laravel-web-analytics.multi_tenancy.tenant_resolver', fn () => 'tenant-a');

        LwaLogs::create($this->log(['tenant_id' => 'tenant-a']));
        LwaLogs::create($this->log(['tenant_id' => 'tenant-b']));

        $this->post('/web-analytics/settings/empty', ['confirm' => 'DELETE'])->assertRedirect();

        $this->assertDatabaseMissing('lwa_logs', ['tenant_id' => 'tenant-a']);
        $this->assertDatabaseHas('lwa_logs', ['tenant_id' => 'tenant-b']);
    }

    public function test_destructive_actions_can_be_disabled(): void
    {
        config()->set('laravel-web-analytics.settings.destructive_actions_enabled', false);

        $this->post('/web-analytics/settings/prune', ['days' => 30])->assertForbidden();
        $this->post('/web-analytics/settings/empty', ['confirm' => 'DELETE'])->assertForbidden();
    }

    public function test_prune_clamps_nonpositive_retention(): void
    {
        LwaLogs::create($this->log(['created_at' => now()->subDays(3)]));

        $this->artisan('lwa:prune', ['--days' => 0])->assertExitCode(0);

        $this->assertSame(0, LwaLogs::count());
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function log(array $overrides = []): array
    {
        return array_merge([
            'ip_address'       => '127.0.0.1',
            'session_id'       => 's_test',
            'method'           => 'GET',
            'path'             => '/',
            'status_code'      => 200,
            'response_time_ms' => 12.5,
            'bytes_sent'       => 1024,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides);
    }
}
