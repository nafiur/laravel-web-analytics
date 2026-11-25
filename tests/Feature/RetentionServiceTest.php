<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Data\RetentionQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\RetentionService;
use Nafiur\WebAnalytics\Tests\TestCase;

class RetentionServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Retention', 'domain' => 'retention.test', 'tracking_id' => 'trk_retention',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function visitor(string $hash): Visitor
    {
        return Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => $hash,
            'first_seen_at' => CarbonImmutable::now()->subDays(30),
            'last_seen_at' => CarbonImmutable::now(),
        ]);
    }

    private function event(string $name, ?int $visitorId, CarbonImmutable $at): void
    {
        AnalyticEvent::create([
            'website_id' => $this->websiteId, 'visitor_id' => $visitorId,
            'name' => $name, 'properties' => [], 'value' => null,
            'created_at' => $at, 'request_uuid' => uniqid('retention-', true),
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function report(array $overrides = []): array
    {
        return app(RetentionService::class)->retention(RetentionQuery::fromArray(array_merge([
            'website_id' => $this->websiteId,
            'from' => CarbonImmutable::now()->startOfDay()->subDays(6)->toIso8601String(),
            'to' => CarbonImmutable::now()->startOfDay()->endOfDay()->toIso8601String(),
        ], $overrides)));
    }

    /** @param array<string, mixed> $result */
    private function cohort(array $result, string $key): array
    {
        foreach ($result['cohorts'] as $cohort) {
            if ($cohort['key'] === $key) {
                return $cohort;
            }
        }

        $this->fail('Cohort ' . $key . ' was not returned.');
    }

    /** @param array<string, mixed> $result */
    private function lifecycle(array $result, string $key): array
    {
        foreach ($result['lifecycle'] as $row) {
            if ($row['key'] === $key) {
                return $row;
            }
        }

        $this->fail('Lifecycle bucket ' . $key . ' was not returned.');
    }

    public function test_daily_cohorts_count_each_user_once_per_period(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $a = $this->visitor('r_a')->id;
        $b = $this->visitor('r_b')->id;
        $c = $this->visitor('r_c')->id;

        $this->event('signup', $a, $today->subDays(4)->addHours(2));
        $this->event('purchase', $a, $today->subDays(3)->addHours(3));
        $this->event('purchase', $a, $today->subDays(2)->addHours(4));
        $this->event('purchase', $a, $today->subDays(2)->addHours(5));
        $this->event('signup', $b, $today->subDays(4)->addHours(6));
        $this->event('signup', $c, $today->subDays(3)->addHours(7));

        $result = $this->report();
        $oldest = $this->cohort($result, $today->subDays(4)->format('Y-m-d'));

        $this->assertSame(2, $oldest['users']);
        $this->assertSame([2, 1, 1], array_column(array_slice($oldest['cells'], 0, 3), 'users'));
        $this->assertSame([100.0, 50.0, 50.0], array_column(array_slice($oldest['cells'], 0, 3), 'rate'));
        $this->assertSame(2, $result['summary']['cohorts']);
        $this->assertSame(3, $result['summary']['cohorted_users']);
        // 1 returning user out of 3 cohorted users across both observed cohorts.
        $this->assertSame(33.33, $result['summary']['period_one_rate']);
        $this->assertSame(6, $result['sample_size']);
    }

    public function test_unelapsed_periods_are_null_not_zero(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $user = $this->visitor('r_today')->id;
        $this->event('signup', $user, $today->addHours(1));

        $cohort = $this->cohort($this->report(), $today->format('Y-m-d'));

        $this->assertSame(1, $cohort['users']);
        $this->assertTrue($cohort['cells'][0]['observed']);
        $this->assertFalse($cohort['cells'][1]['observed']);
        $this->assertNull($cohort['cells'][1]['users']);
        $this->assertNull($cohort['cells'][1]['rate']);
        $this->assertNull($this->report()['summary']['period_one_rate']);
    }

    public function test_aliased_anonymous_activity_returns_to_the_same_profile(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $anon = $this->visitor('r_anon')->id;
        $known = $this->visitor('r_known')->id;
        IdentityAlias::create([
            'website_id' => $this->websiteId, 'anonymous_id' => 'r_anon',
            'distinct_id' => 'r_known', 'aliased_at' => $today,
        ]);

        $this->event('signup', $anon, $today->subDays(4)->addHours(2));
        $this->event('purchase', $known, $today->subDays(3)->addHours(3));

        $cohort = $this->cohort($this->report(), $today->subDays(4)->format('Y-m-d'));

        $this->assertSame(1, $cohort['users']);
        $this->assertSame(1, $cohort['cells'][1]['users']);
        $this->assertSame(100.0, $cohort['cells'][1]['rate']);
    }

    public function test_lifecycle_classifies_new_returning_resurrecting_and_dormant_users(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $returning = $this->visitor('r_returning')->id;
        $dormant = $this->visitor('r_dormant')->id;
        $new = $this->visitor('r_new')->id;
        $resurrecting = $this->visitor('r_resurrecting')->id;

        $this->event('pageview', $returning, $today->subDays(4)->addHour());
        $this->event('pageview', $returning, $today->subDays(3)->addHour());
        $this->event('pageview', $dormant, $today->subDays(4)->addHour());
        $this->event('pageview', $new, $today->subDays(3)->addHour());
        $this->event('pageview', $resurrecting, $today->subDays(5)->addHour());
        $this->event('pageview', $resurrecting, $today->subDays(3)->addHour());

        $row = $this->lifecycle($this->report(), $today->subDays(3)->format('Y-m-d'));

        $this->assertSame(1, $row['new']);
        $this->assertSame(1, $row['returning']);
        $this->assertSame(1, $row['resurrecting']);
        $this->assertSame(1, $row['dormant']);
    }

    public function test_weekly_cohorts_use_iso_weeks(): void
    {
        $monday = CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY);
        $user = $this->visitor('r_week')->id;

        $this->event('signup', $user, $monday->addHours(2));
        $this->event('purchase', $user, $monday->addDays(2)->addHours(2));
        $this->event('purchase', $user, $monday->addWeek()->addHours(2));

        $result = $this->report([
            'granularity' => 'week',
            'from' => $monday->subDay()->toIso8601String(),
            'to' => $monday->addWeeks(2)->endOfWeek(CarbonImmutable::SUNDAY)->toIso8601String(),
        ]);
        $cohort = $this->cohort($result, $monday->format('o-W'));

        $this->assertSame(1, $cohort['users']);
        $this->assertSame(1, $cohort['cells'][0]['users']);
        $this->assertSame(1, $cohort['cells'][1]['users']);
        $this->assertSame('week', $result['granularity']);
    }

    public function test_sample_cap_and_events_without_visitors_are_documented(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $user = $this->visitor('r_cap')->id;
        $this->event('signup', $user, $today->subDays(3)->addHours(2));
        $this->event('purchase', $user, $today->subDays(2)->addHours(2));
        $this->event('purchase', null, $today->subDays(2)->addHours(3));

        $result = $this->report(['max_events' => 2]);

        $this->assertTrue($result['capped']);
        $this->assertSame(2, $result['sample_size']);
        $this->assertStringContainsString('lower bound', implode(' ', $result['definitions']));
        $this->assertStringContainsString('without a visitor id', implode(' ', $result['definitions']));
    }
}
