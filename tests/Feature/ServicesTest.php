<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Nafiur\WebAnalytics\Mail\WeeklySummaryMail;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\AlertService;
use Nafiur\WebAnalytics\Services\GeoIpService;
use Nafiur\WebAnalytics\Services\PruneService;
use Nafiur\WebAnalytics\Services\WebAnalyticsLogParser;
use Nafiur\WebAnalytics\Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        if (!empty($this->logFile) && file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        parent::tearDown();
    }

    /** @var string|null */
    private $logFile;

    public function test_geoip_lookup_uses_configured_provider_and_caches_result(): void
    {
        config()->set('laravel-web-analytics.geoip.enabled', true);
        config()->set('laravel-web-analytics.geoip.provider', 'ip-api');

        Http::fake([
            'ip-api.com/*' => Http::response([
                'status'      => 'success',
                'country'     => 'Bangladesh',
                'countryCode' => 'BD',
                'city'        => 'Dhaka',
            ]),
        ]);

        $first = GeoIpService::resolve('8.8.8.8');
        $second = GeoIpService::resolve('8.8.8.8');

        $this->assertSame('BD', $first['country_code']);
        $this->assertSame('Bangladesh', $first['country_name']);
        $this->assertSame('Dhaka', $first['city']);
        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_geoip_skips_private_addresses(): void
    {
        config()->set('laravel-web-analytics.geoip.enabled', true);
        config()->set('laravel-web-analytics.geoip.provider', 'ip-api');
        Http::fake();

        $this->assertSame([
            'country_code' => null,
            'country_name' => null,
            'city'         => null,
            'latitude'     => null,
            'longitude'    => null,
        ], GeoIpService::resolve('192.168.1.10'));
        Http::assertNothingSent();
    }

    public function test_error_alerts_are_deduplicated(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', true);
        config()->set('laravel-web-analytics.alerts.channel', 'slack');
        config()->set('laravel-web-analytics.alerts.webhook_url', 'https://example.test/hook');
        Http::fake();

        AlertService::notifyErrorSpike(500, 'https://example.test/a', '1.2.3.4');
        AlertService::notifyErrorSpike(500, 'https://example.test/a', '1.2.3.4');

        Http::assertSentCount(1);
    }

    public function test_error_spike_alert_respects_threshold_and_fires_once(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', true);
        config()->set('laravel-web-analytics.alerts.channel', 'discord');
        config()->set('laravel-web-analytics.alerts.webhook_url', 'https://example.test/hook');
        config()->set('laravel-web-analytics.alerts.error_rate_threshold_percentage', 10);
        Http::fake();

        for ($i = 0; $i < 10; $i++) {
            AlertService::recordRequest($i < 5);
        }

        AlertService::maybeNotifySpike('https://example.test/a', '1.2.3.4');
        AlertService::maybeNotifySpike('https://example.test/b', '1.2.3.4');

        Http::assertSentCount(1);
    }

    public function test_alert_payload_strips_markdown_from_untrusted_values(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', true);
        config()->set('laravel-web-analytics.alerts.channel', 'slack');
        config()->set('laravel-web-analytics.alerts.webhook_url', 'https://example.test/hook');
        Http::fake();

        AlertService::notifyErrorSpike(500, 'https://example.test/a`_*[x]', '1.2.3.4`');

        $requests = Http::recorded();
        $this->assertCount(1, $requests);

        $text = (string) ($requests[0][0]->data()['text'] ?? '');
        $this->assertStringNotContainsString('`_*[x]', $text);
        $this->assertStringContainsString('https://example.test/ax', $text);
        $this->assertStringContainsString('`1.2.3.4`', $text);
    }

    public function test_maxmind_provider_falls_back_safely_without_reader(): void
    {
        config()->set('laravel-web-analytics.geoip.provider', 'maxmind');
        config()->set('laravel-web-analytics.geoip.maxmind_db_path', '/nonexistent/GeoLite2-City.mmdb');

        $result = GeoIpService::resolve('8.8.8.8');

        $this->assertNull($result['country_code']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }

    public function test_weekly_summary_can_be_emailed(): void
    {
        config()->set('laravel-web-analytics.alerts.enabled', true);
        config()->set('laravel-web-analytics.alerts.email', 'owner@example.test');
        config()->set('laravel-web-analytics.alerts.channel', 'slack');
        config()->set('laravel-web-analytics.alerts.webhook_url', null);
        Http::fake();
        Mail::fake();
        AlertService::sendSummary([
            'total_requests_fmt'  => '1.2K',
            'unique_visitors_fmt' => '900',
            'total_bytes_fmt'     => '40 MB',
            'avg_response_time'   => 120,
            'error_rate'          => 1.5,
        ]);

        Mail::assertSent(WeeklySummaryMail::class);
    }

    public function test_prune_service_scopes_deletion_to_tenant(): void
    {
        LwaLogs::create([
            'tenant_id'  => 'a',
            'path'       => '/old',
            'created_at' => now()->subDays(40),
        ]);
        LwaLogs::create([
            'tenant_id'  => 'b',
            'path'       => '/old',
            'created_at' => now()->subDays(40),
        ]);

        $deleted = (new PruneService())->prune(30, 'a');

        $this->assertSame(1, $deleted);
        $this->assertSame(1, LwaLogs::count());
        $this->assertDatabaseHas('lwa_logs', ['tenant_id' => 'b']);
    }

    public function test_file_log_parser_reads_combined_access_log(): void
    {
        $this->logFile = tempnam(sys_get_temp_dir(), 'lwa_access_');
        $stamp = now()->format('d/M/Y:H:i:s O');
        file_put_contents($this->logFile, implode(PHP_EOL, [
            '103.230.104.10 - - [' . $stamp . '] "GET /posts?x=1 HTTP/1.1" 200 2048 "https://ref.test" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/129.0"',
            '8.8.8.8 - - [' . $stamp . '] "POST /api/x HTTP/1.1" 500 512 "-" "curl/8.0"',
        ]));

        $analytics = (new WebAnalyticsLogParser($this->logFile))->getDashboardData('24h');

        $this->assertSame(2, $analytics['summary']['total_requests']);
        $this->assertSame(1, $analytics['summary']['status_counts']['5xx']);
        $this->assertSame(2, count($analytics['breakdowns']['top_countries']));
        $bd = collect($analytics['breakdowns']['top_countries'])->firstWhere('name', 'Bangladesh');
        $this->assertNotSame('', $bd['flag']);
    }
}
