<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Data\PathQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\PathService;
use Nafiur\WebAnalytics\Tests\TestCase;

class PathServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Paths', 'domain' => 'paths.test', 'tracking_id' => 'trk_paths',
        ]);
        $this->websiteId = (int) $website->id;
        $this->today = CarbonImmutable::now()->startOfDay();

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function visitor(string $hash): Visitor
    {
        return Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => $hash,
            'first_seen_at' => $this->today->subDays(14),
            'last_seen_at' => $this->today,
        ]);
    }

    /** @param array<string, mixed> $properties */
    private function event(string $name, ?int $visitorId, CarbonImmutable $at, array $properties = []): void
    {
        AnalyticEvent::create([
            'website_id' => $this->websiteId, 'visitor_id' => $visitorId,
            'name' => $name, 'properties' => $properties, 'value' => null,
            'created_at' => $at, 'request_uuid' => uniqid('path-', true),
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function report(array $overrides = []): array
    {
        return app(PathService::class)->paths(PathQuery::fromArray(array_merge([
            'website_id' => $this->websiteId,
            'from' => $this->today->subDays(7)->toIso8601String(),
            'to' => $this->today->endOfDay()->toIso8601String(),
        ], $overrides)));
    }

    public function test_paths_collapse_repeats_and_count_each_user_once(): void
    {
        $a = $this->visitor('p_a')->id;
        $b = $this->visitor('p_b')->id;
        $c = $this->visitor('p_c')->id;

        $this->event('pageview', $a, $this->today->subDays(5)->addHours(2));
        $this->event('pageview', $a, $this->today->subDays(5)->addHours(3));
        $this->event('signup', $a, $this->today->subDays(5)->addHours(4));
        $this->event('purchase', $a, $this->today->subDays(4)->addHours(5));
        $this->event('pageview', $b, $this->today->subDays(4)->addHours(6));
        $this->event('purchase', $b, $this->today->subDays(3)->addHours(7));
        $this->event('purchase', $c, $this->today->subDays(3)->addHours(8));

        $result = $this->report();

        $this->assertSame(2, $result['summary']['sequenced_users']);
        $this->assertSame(1, $result['summary']['single_step_users']);
        $this->assertSame(2, $result['summary']['paths_found']);
        $this->assertSame([
            ['pageview', 'purchase'],
            ['pageview', 'signup', 'purchase'],
        ], array_column($result['paths'], 'steps'));
        $this->assertSame([1, 1], array_column($result['paths'], 'users'));
        $this->assertSame([50.0, 50.0], array_column($result['paths'], 'share'));
        $this->assertSame(50.0, $result['summary']['top_path_share']);
        $this->assertSame(7, $result['sample_size']);

        $first = $result['positions'][0];
        $this->assertSame(1, $first['position']);
        $this->assertSame('pageview', $first['events'][0]['name']);
        $this->assertSame(2, $first['events'][0]['users']);
        $this->assertSame(100.0, $first['events'][0]['share']);
    }

    public function test_start_and_end_events_limit_the_sequence(): void
    {
        $starter = $this->visitor('p_start')->id;
        $other = $this->visitor('p_other')->id;

        $this->event('pageview', $other, $this->today->subDays(5)->addHours(1));
        $this->event('purchase', $other, $this->today->subDays(4)->addHours(1));
        $this->event('signup', $starter, $this->today->subDays(4)->addHours(2));
        $this->event('pageview', $starter, $this->today->subDays(3)->addHours(2));
        $this->event('purchase', $starter, $this->today->subDays(2)->addHours(2));
        $this->event('logout', $starter, $this->today->subDays(1)->addHours(2));

        $result = $this->report([
            'start_event' => 'signup',
            'end_event' => 'purchase',
            'depth' => 3,
        ]);

        $this->assertSame(1, $result['summary']['excluded_users']);
        $this->assertSame(1, $result['summary']['sequenced_users']);
        $this->assertSame([['signup', 'pageview', 'purchase']], array_column($result['paths'], 'steps'));
        $this->assertStringContainsString('signup', $result['definitions'][1]);
        $this->assertStringContainsString('purchase', $result['definitions'][1]);
    }

    public function test_aliased_anonymous_and_known_ids_form_one_path(): void
    {
        $anon = $this->visitor('p_anon')->id;
        $known = $this->visitor('p_known')->id;
        IdentityAlias::create([
            'website_id' => $this->websiteId, 'anonymous_id' => 'p_anon',
            'distinct_id' => 'p_known', 'aliased_at' => $this->today->subDays(3),
        ]);

        $this->event('pageview', $anon, $this->today->subDays(4)->addHours(1));
        $this->event('signup', $known, $this->today->subDays(3)->addHours(1));
        $this->event('purchase', $known, $this->today->subDays(2)->addHours(1));

        $result = $this->report();

        $this->assertSame(1, $result['summary']['sequenced_users']);
        $this->assertSame([['pageview', 'signup', 'purchase']], array_column($result['paths'], 'steps'));
        $this->assertSame(100.0, $result['summary']['top_path_share']);
    }

    public function test_property_filter_sample_cap_and_visitorless_events_are_documented(): void
    {
        $a = $this->visitor('p_prop_a')->id;
        $b = $this->visitor('p_prop_b')->id;

        $this->event('pageview', null, $this->today->subDays(6)->addHours(1), ['plan' => 'pro']);
        $this->event('pageview', $b, $this->today->subDays(5)->addHours(1), ['plan' => 'free']);
        $this->event('signup', $a, $this->today->subDays(4)->addHours(1), ['plan' => 'pro']);
        $this->event('purchase', $a, $this->today->subDays(3)->addHours(1), ['plan' => 'pro']);

        $result = $this->report([
            'property_key' => 'plan',
            'property_value' => 'pro',
            'max_events' => 2,
        ]);

        $this->assertTrue($result['capped']);
        $this->assertSame(2, $result['sample_size']);
        $this->assertSame(1, $result['summary']['sequenced_users']);
        $this->assertSame([['signup', 'purchase']], array_column($result['paths'], 'steps'));
        $notes = implode(' ', $result['definitions']);
        $this->assertStringContainsString('Property filter: `plan` = `pro`', $notes);
        $this->assertStringContainsString('lower bound', $notes);
        $this->assertStringContainsString('without a visitor id', $notes);
    }

    public function test_query_bounds_depth_limit_and_share_precision(): void
    {
        $query = PathQuery::fromArray([
            'website_id' => 4,
            'depth' => 99,
            'limit' => 99,
            'max_events' => 999999,
        ]);

        $this->assertSame(8, $query->depth);
        $this->assertSame(50, $query->limit);
        $this->assertSame(50000, $query->maxEvents);
    }
}
