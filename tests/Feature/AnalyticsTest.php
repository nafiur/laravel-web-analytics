<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertStringContainsString('id="sidebarOverview"', $content);
        $this->assertSame(5, substr_count($content, 'data-sidebar-tab='));

        foreach (['tab-requests', 'tab-visitors', 'tab-performance', 'tab-security', 'tab-live', 'tab-settings'] as $pane) {
            $this->assertStringContainsString('id="' . $pane . '"', $content);
        }

        foreach (['AI Crawl Control', 'Web3', 'Manage account', 'Back to Domains'] as $removed) {
            $this->assertStringNotContainsString($removed, $content);
        }
    }

    public function test_settings_page_renders_all_sections(): void
    {
        $content = $this->get('/web-analytics')->getContent();

        foreach ([
            'settings-general', 'settings-dashboard', 'settings-collection',
            'settings-maintenance', 'settings-about', 'settingLanguage',
            'settingAppearance', 'settingTimezone', '/web-analytics/settings/prune',
            '/web-analytics/settings/empty', 'value="hi"', 'Asia/Tokyo',
        ] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }

        $this->assertStringNotContainsString('Display name', $content);
    }

    public function test_time_series_exposes_timestamps_and_granularity(): void
    {
        LwaLogs::create($this->log(['created_at' => now()]));

        $series = $this->getJson('/web-analytics/data?range=1h')->json('analytics.time_series');

        $this->assertSame('hour', $series['granularity']);
        $this->assertSame(count($series['labels']), count($series['timestamps']));
        $this->assertNotEmpty($series['timestamps'][0]);
    }

    public function test_settings_page_includes_alerts_and_export_sections(): void
    {
        $content = $this->get('/web-analytics')->getContent();

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
