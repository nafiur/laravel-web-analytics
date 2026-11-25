<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Data\InsightQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\InsightService;
use Nafiur\WebAnalytics\Tests\TestCase;

class InsightServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Insights', 'domain' => 'insights.test', 'tracking_id' => 'trk_insights',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function visitor(string $hash): Visitor
    {
        return Visitor::create([
            'website_id'     => $this->websiteId,
            'visitor_hash'   => $hash,
            'first_seen_at'  => CarbonImmutable::now()->subDays(3),
            'last_seen_at'   => CarbonImmutable::now(),
        ]);
    }

    /** @param array<string, mixed> $properties */
    private function event(string $name, array $properties, ?float $value, int $visitorId, CarbonImmutable $at): void
    {
        AnalyticEvent::create([
            'website_id'   => $this->websiteId,
            'visitor_id'   => $visitorId,
            'name'         => $name,
            'properties'   => $properties,
            'value'        => $value,
            'created_at'   => $at,
            'request_uuid' => uniqid('insight-', true),
        ]);
    }

    public function test_events_metric_counts_daily_buckets_including_empty_ones(): void
    {
        $visitor = $this->visitor('f_a');
        $now = CarbonImmutable::now();
        $this->event('purchase', [], 10, $visitor->id, $now->subDays(2));
        $this->event('purchase', [], 5, $visitor->id, $now);

        $result = app(InsightService::class)->trend(InsightQuery::fromArray([
            'website_id'  => $this->websiteId,
            'metric'      => 'events',
            'name'        => 'purchase',
            'from'        => $now->subDays(3)->toIso8601String(),
            'to'          => $now->toIso8601String(),
            'granularity' => 'day',
        ]));

        $this->assertCount(4, $result['labels']);
        $this->assertSame(2, $result['global_total']);
        $this->assertSame(2, array_sum($result['series'][0]['data']));
        $this->assertSame(2, $result['sample_size']);
        $this->assertSame('day', $result['granularity']);
        $this->assertNotEmpty($result['definitions']);
    }

    public function test_users_metric_folds_aliased_anonymous_ids_into_one_profile(): void
    {
        $anonymous = $this->visitor('f_anon');
        $known = $this->visitor('u_7');
        IdentityAlias::create([
            'website_id'   => $this->websiteId,
            'anonymous_id' => 'f_anon',
            'distinct_id'  => 'u_7',
            'aliased_at'   => CarbonImmutable::now(),
        ]);

        $now = CarbonImmutable::now();
        $this->event('signup', [], null, $anonymous->id, $now->subHour());
        $this->event('purchase', [], null, $known->id, $now);

        $result = app(InsightService::class)->trend(InsightQuery::fromArray([
            'website_id'  => $this->websiteId,
            'metric'      => 'users',
            'from'        => $now->subDay()->toIso8601String(),
            'to'          => $now->toIso8601String(),
            'granularity' => 'day',
        ]));

        $this->assertSame(1, $result['global_total']);
        $this->assertSame(2, $result['sample_size']);
    }

    public function test_breakdown_and_property_filters_are_applied(): void
    {
        $visitor = $this->visitor('f_b');
        $now = CarbonImmutable::now();
        $this->event('purchase', ['plan' => 'pro'], 100, $visitor->id, $now);
        $this->event('purchase', ['plan' => 'free'], 5, $visitor->id, $now);

        $result = app(InsightService::class)->trend(InsightQuery::fromArray([
            'website_id'    => $this->websiteId,
            'metric'        => 'value',
            'breakdown'     => 'plan',
            'property_key'  => 'plan',
            'property_value'=> 'pro',
            'from'          => $now->subDay()->toIso8601String(),
            'to'            => $now->toIso8601String(),
            'granularity'   => 'day',
        ]));

        $this->assertSame(100.0, $result['global_total']);
        $this->assertCount(1, $result['series']);
        $this->assertSame('pro', $result['series'][0]['name']);
    }

    public function test_long_hourly_windows_are_coarsened_and_documented(): void
    {
        $visitor = $this->visitor('f_c');
        $now = CarbonImmutable::now();
        $this->event('visit', [], null, $visitor->id, $now->subDays(5));

        $result = app(InsightService::class)->trend(InsightQuery::fromArray([
            'website_id'  => $this->websiteId,
            'granularity' => 'hour',
            'from'        => $now->subDays(20)->toIso8601String(),
            'to'          => $now->toIso8601String(),
        ]));

        $this->assertSame('day', $result['granularity']);
        $this->assertSame('hour', $result['granularity_requested']);
        $this->assertCount(21, $result['labels']);
    }
}
