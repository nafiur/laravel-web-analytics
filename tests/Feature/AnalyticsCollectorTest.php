<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\VisitContext;
use Nafiur\WebAnalytics\Http\Middleware\TrackWebTraffic;
use Nafiur\WebAnalytics\Jobs\RecordPageview;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticsSession;
use Nafiur\WebAnalytics\Models\Analytics\Pageview;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AnalyticsCollectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Example', 'domain' => 'example.test', 'tracking_id' => 'trk_1',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $website->id);
    }

    public function test_sync_collector_creates_visitor_session_and_pageviews(): void
    {
        $collector = app(Collector::class);
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');

        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'visitor-a', path: '/a',
            occurredAt: now()->subMinutes(5)->toIso8601String(),
        ));
        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'visitor-a', path: '/b',
            occurredAt: now()->toIso8601String(),
        ));

        $this->assertSame(1, Visitor::count());
        $this->assertSame(1, AnalyticsSession::count());
        $this->assertSame(2, Pageview::count());

        $session = AnalyticsSession::first();
        $this->assertSame(2, $session->pageviews_count);
        $this->assertFalse($session->is_bounce);
        $this->assertGreaterThan(0, $session->duration_seconds);
    }

    public function test_idle_gap_starts_a_new_session(): void
    {
        $collector = app(Collector::class);
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');

        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'visitor-b', path: '/first',
            occurredAt: now()->subHours(3)->toIso8601String(),
        ));
        $collector->pageview(new VisitContext(
            websiteId: $websiteId, visitorHash: 'visitor-b', path: '/second',
            occurredAt: now()->toIso8601String(),
        ));

        $this->assertSame(1, Visitor::count());
        $this->assertSame(2, AnalyticsSession::count());
    }

    public function test_event_properties_are_validated_and_stored(): void
    {
        app(Collector::class)->event(new EventData(
            websiteId: (int) config('laravel-web-analytics.analytics.website_id'),
            name: 'purchase',
            visitorHash: 'visitor-c',
            properties: ['amount' => 1500, 'password' => 'secret', 'email' => 'a@b.test'],
            value: 1500,
        ));

        $event = AnalyticEvent::first();

        $this->assertNotNull($event);
        $this->assertSame('purchase', $event->name);
        $this->assertSame(['amount' => 1500], $event->properties);
        $this->assertNotNull($event->visitor_id);
    }

    public function test_queue_mode_dispatches_jobs(): void
    {
        Queue::fake();
        config()->set('laravel-web-analytics.analytics.mode', 'queue');

        app(Collector::class)->pageview(new VisitContext(
            websiteId: (int) config('laravel-web-analytics.analytics.website_id'),
            visitorHash: 'visitor-d',
            path: '/queued',
        ));

        Queue::assertPushed(RecordPageview::class);
        $this->assertSame(0, Pageview::count());
    }

    public function test_tracking_middleware_feeds_the_advanced_collector(): void
    {
        $request = Request::create('/advanced-path', 'GET', [], [], [], [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/129.0 Safari/537.36',
        ]);
        $response = new SymfonyResponse('ok', 200, ['Content-Length' => 2]);

        $middleware = new TrackWebTraffic();
        $middleware->handle($request, fn () => $response);
        $middleware->terminate($request, $response);

        $this->assertSame(1, Pageview::count());
        $this->assertSame('/advanced-path', Pageview::first()->path);
    }
}
