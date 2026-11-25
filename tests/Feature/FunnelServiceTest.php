<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Data\FunnelQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\FunnelService;
use Nafiur\WebAnalytics\Tests\TestCase;

class FunnelServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Funnels', 'domain' => 'funnels.test', 'tracking_id' => 'trk_funnels',
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
            'first_seen_at' => CarbonImmutable::now()->subDays(10),
            'last_seen_at' => CarbonImmutable::now(),
        ]);
    }

    private function event(string $name, int $visitorId, CarbonImmutable $at): void
    {
        AnalyticEvent::create([
            'website_id' => $this->websiteId, 'visitor_id' => $visitorId,
            'name' => $name, 'properties' => [], 'value' => null,
            'created_at' => $at, 'request_uuid' => uniqid('funnel-', true),
        ]);
    }

    /** @param array<int, string> $steps */
    private function funnel(array $steps, string $window = '7d', int $maxEvents = 20000): array
    {
        return app(FunnelService::class)->funnel(FunnelQuery::fromArray([
            'website_id' => $this->websiteId,
            'steps' => $steps,
            'window' => $window,
            'max_events' => $maxEvents,
        ]));
    }

    public function test_ordered_steps_and_rates_are_computed_per_user(): void
    {
        $now = CarbonImmutable::now();
        $u1 = $this->visitor('u_1')->id;
        $u2 = $this->visitor('u_2')->id;
        $u3 = $this->visitor('u_3')->id;

        $this->event('view_item', $u1, $now->subHours(2));
        $this->event('add_to_cart', $u1, $now->subHour());
        $this->event('purchase', $u1, $now);

        $this->event('view_item', $u2, $now->subHours(3));

        $this->event('view_item', $u3, $now->subHours(4));
        $this->event('purchase', $u3, $now->subHours(3));

        $result = $this->funnel(['view_item', 'add_to_cart', 'purchase']);

        $this->assertSame([3, 1, 1], array_column($result['steps'], 'users'));
        $this->assertSame(1, $result['completed_users']);
        $this->assertSame(33.33, $result['overall_conversion_rate']);
        $this->assertSame(33.33, $result['steps'][1]['step_conversion_rate']);
        $this->assertSame(2, $result['steps'][1]['drop_off']);
        $this->assertSame(0, $result['steps'][2]['drop_off']);
        $this->assertSame(7200, $result['median_seconds']);
        $this->assertSame(6, $result['sample_size']);
    }

    public function test_conversion_window_can_include_or_exclude_a_late_step(): void
    {
        $now = CarbonImmutable::now();
        $user = $this->visitor('u_w')->id;
        $this->event('signup', $user, $now->subDays(9));
        $this->event('purchase', $user, $now);

        $short = $this->funnel(['signup', 'purchase'], '7d');
        $long = $this->funnel(['signup', 'purchase'], '30d');

        $this->assertSame(0, $short['completed_users']);
        $this->assertSame(1, $long['completed_users']);
    }

    public function test_aliased_anonymous_activity_counts_as_one_user(): void
    {
        $now = CarbonImmutable::now();
        $anon = $this->visitor('f_anon')->id;
        $known = $this->visitor('u_7')->id;
        IdentityAlias::create([
            'website_id' => $this->websiteId, 'anonymous_id' => 'f_anon',
            'distinct_id' => 'u_7', 'aliased_at' => $now,
        ]);

        $this->event('signup', $anon, $now->subHours(3));
        $this->event('add_to_cart', $known, $now->subHours(2));
        $this->event('purchase', $known, $now->subHour());

        $result = $this->funnel(['signup', 'add_to_cart', 'purchase']);

        $this->assertSame([1, 1, 1], array_column($result['steps'], 'users'));
        $this->assertSame(100.0, $result['overall_conversion_rate']);
    }

    public function test_repeated_events_do_not_inflate_step_counts(): void
    {
        $now = CarbonImmutable::now();
        $user = $this->visitor('u_repeat')->id;

        $this->event('view_item', $user, $now->subMinutes(3));
        $this->event('view_item', $user, $now->subMinutes(2));
        $this->event('purchase', $user, $now->subMinute());

        $result = $this->funnel(['view_item', 'purchase']);

        $this->assertSame(1, $result['entered_users']);
        $this->assertSame(1, $result['completed_users']);
        $this->assertSame(60, $result['median_seconds']);
    }

    public function test_sample_cap_is_reported_and_invalid_steps_do_not_query(): void
    {
        $now = CarbonImmutable::now();
        $user = $this->visitor('u_cap')->id;
        $this->event('a', $user, $now->subMinutes(3));
        $this->event('b', $user, $now->subMinutes(2));
        $this->event('c', $user, $now->subMinute());

        $capped = $this->funnel(['a', 'b', 'c'], 'all', 2);
        $this->assertTrue($capped['capped']);
        $this->assertSame(2, $capped['sample_size']);
        $this->assertSame(0, $capped['completed_users']);
        $this->assertStringContainsString('lower bound', implode(' ', $capped['definitions']));

        $invalid = $this->funnel(['a']);
        $this->assertSame([], $invalid['steps']);
        $this->assertSame(0, $invalid['sample_size']);
        $this->assertStringContainsString('At least two', implode(' ', $invalid['definitions']));
    }
}
