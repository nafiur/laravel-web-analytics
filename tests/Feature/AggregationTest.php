<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Data\VisitContext;
use Nafiur\WebAnalytics\Models\Analytics\HourlyStat;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AggregationService;
use Nafiur\WebAnalytics\Services\MetricService;
use Nafiur\WebAnalytics\Tests\TestCase;

class AggregationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Example', 'domain' => 'example.test', 'tracking_id' => 'trk_agg',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $website->id);
    }

    public function test_aggregation_builds_hourly_rollups_from_raw_events(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        $collector = app(Collector::class);

        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'agg-visitor', path: '/a',
            statusCode: 200, bytesSent: 100, responseTimeMs: 50,
            occurredAt: now()->subMinutes(4)->toIso8601String(),
        ));
        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'agg-visitor', path: '/error',
            statusCode: 500, bytesSent: 100, responseTimeMs: 150,
            occurredAt: now()->subMinutes(2)->toIso8601String(),
        ));

        app(AggregationService::class)->aggregate($websiteId, now(), 'hourly');

        $stat = HourlyStat::first();
        $this->assertNotNull($stat);
        $this->assertSame(2, $stat->pageviews);
        $this->assertSame(1, $stat->visitors);
        $this->assertSame(1, $stat->sessions);
        $this->assertSame(0, $stat->bounces);
        $this->assertSame(200, $stat->bytes);
        $this->assertSame(1, $stat->errors_5xx);
    }

    public function test_metric_service_reads_rollups_only(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        $collector = app(Collector::class);

        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'metric-visitor', path: '/only',
            statusCode: 200, bytesSent: 0, occurredAt: now()->toIso8601String(),
        ));

        $stats = app(AggregationService::class);
        $stats->aggregate($websiteId, now(), 'hourly');

        $summary = app(MetricService::class)->summary($websiteId, '1h');

        $this->assertSame(1, $summary['total_requests']);
        $this->assertSame(1, $summary['sessions']);
        $this->assertSame(1, $summary['unique_visitors']);
        $this->assertSame(100.0, $summary['bounce_rate']);
        $this->assertSame('Aggregated analytics', $summary['storage_type']);
    }

    public function test_aggregate_command_creates_rollups(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');

        app(Collector::class)->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'cmd-visitor', path: '/cmd',
            occurredAt: now()->toIso8601String(),
        ));

        $this->artisan('lwa:aggregate', [
            '--website' => $websiteId,
            '--from' => now()->subHour()->toIso8601String(),
            '--to' => now()->toIso8601String(),
            '--granularity' => 'hourly',
        ])->assertExitCode(0);

        $this->assertGreaterThanOrEqual(1, HourlyStat::count());
    }
}
