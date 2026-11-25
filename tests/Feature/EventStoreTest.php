<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\Aggregation;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\PruneWindow;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class EventStoreTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Example', 'domain' => 'example.test', 'tracking_id' => 'trk_1',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $website->id);

        $this->websiteId = (int) $website->id;
    }

    private function event(?string $uuid = 'evt-1', ?string $name = 'purchase', ?float $value = 42.0): UnifiedEvent
    {
        return new UnifiedEvent(
            uuid:         $uuid ?? 'evt-1',
            name:         $name ?? 'purchase',
            timestamp:    CarbonImmutable::now(),
            distinctId:  'f_visitor',
            anonymousId: 'f_visitor',
            sessionId:    null,
            websiteId:   $this->websiteId,
            properties:  ['plan' => 'pro'],
            context:      [],
            value:        $value,
            createdAt:    CarbonImmutable::now(),
        );
    }

    public function test_event_store_inserts_unified_events(): void
    {
        app(EventStore::class)->insert([$this->event('evt-1', 'purchase', 42.0)]);

        $this->assertSame(1, AnalyticEvent::count());
        $event = AnalyticEvent::first();
        $this->assertSame('purchase', $event->name);
        $this->assertSame(42.0, (float) $event->value);
        $this->assertSame(['plan' => 'pro'], $event->properties);
        $this->assertSame('evt-1', $event->request_uuid);
    }

    public function test_event_store_queries_events_by_website_and_name(): void
    {
        app(EventStore::class)->insert([
            $this->event('evt-1', 'purchase', 42.0),
            $this->event('evt-2', 'pageview', 0.0),
        ]);

        $events = app(EventStore::class)->query(new EventQuery(
            websiteId: $this->websiteId,
            name:      'purchase',
        ));

        $this->assertSame(1, $events->count());
        $this->assertSame('purchase', $events->first()->name);
    }

    public function test_event_store_aggregates_stored_events(): void
    {
        app(EventStore::class)->insert([
            $this->event('evt-1', 'purchase', 10.0),
            $this->event('evt-2', 'purchase', 5.0),
        ]);

        $result = app(EventStore::class)->aggregate(new Aggregation(
            metric:      'sum_value',
            websiteId:   $this->websiteId,
            granularity: 'hourly',
        ));

        $this->assertSame(15.0, (float) ($result['sum_value'] ?? 0));
    }

    public function test_event_store_prunes_events_before_the_window(): void
    {
        $old = new UnifiedEvent(
            uuid:         'evt-old',
            name:         'purchase',
            timestamp:    CarbonImmutable::parse('-2 days'),
            distinctId:  'f_visitor',
            anonymousId: 'f_visitor',
            sessionId:    null,
            websiteId:   $this->websiteId,
            properties:  [],
            context:      [],
            value:        1.0,
            createdAt:    CarbonImmutable::parse('-2 days'),
        );
        app(EventStore::class)->insert([$old]);

        $this->assertSame(1, AnalyticEvent::count());

        $pruned = app(EventStore::class)->prune(new PruneWindow(beforeAt: now()->subDay()->toIso8601String()));

        $this->assertSame(1, $pruned);
        $this->assertSame(0, AnalyticEvent::count());
    }
}
