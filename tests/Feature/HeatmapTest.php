<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\HeatmapService;
use Nafiur\WebAnalytics\Tests\TestCase;

class HeatmapTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('viewLwaReplay', fn ($user = null) => true);
        config()->set('laravel-web-analytics.replay.enabled', true);

        $website = Website::create([
            'name'        => 'Heatmap QA',
            'domain'      => 'heatmap.test',
            'tracking_id' => 'trk_heatmap',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function seedSession(string $key, string $url, ?int $websiteId = null): ReplaySession
    {
        return ReplaySession::create([
            'session_key' => $key,
            'website_id'  => $websiteId ?? $this->websiteId,
            'url'         => $url,
            'started_at'  => now()->subDay(),
            'duration_ms' => 6000,
            'event_count' => 4,
            'bytes'       => 0,
            'sample_rate' => 1,
        ]);
    }

    private function seedMarkers(): void
    {
        $first = $this->seedSession('heatmap_session_a', 'checkout');
        foreach ([100, 200, 300] as $at) {
            ReplayMarker::create([
                'replay_session_id' => $first->id,
                'website_id'        => $this->websiteId,
                'type'              => 'click',
                'at_ms'             => $at,
                'payload'           => [
                    'target' => ['tag' => 'button', 'id' => 'pay'],
                    'data'   => ['x' => 500, 'y' => 200, 'vw' => 1000, 'vh' => 1000],
                ],
            ]);
        }
        ReplayMarker::create([
            'replay_session_id' => $first->id,
            'website_id'        => $this->websiteId,
            'type'              => 'scroll',
            'at_ms'             => 400,
            'payload'           => ['target' => ['tag' => 'body'], 'data' => ['y' => 500, 'vh' => 500, 'dh' => 1500]],
        ]);

        $second = $this->seedSession('heatmap_session_b', 'pricing');
        ReplayMarker::create([
            'replay_session_id' => $second->id,
            'website_id'        => $this->websiteId,
            'type'              => 'click',
            'at_ms'             => 5000,
            'payload'           => [
                'target' => ['tag' => 'a', 'id' => 'next'],
                'data'   => ['x' => 250, 'y' => 500, 'vw' => 1000, 'vh' => 1000],
            ],
        ]);
        ReplayMarker::create([
            'replay_session_id' => $second->id,
            'website_id'        => $this->websiteId,
            'type'              => 'scroll',
            'at_ms'             => 8000,
            'payload'           => ['target' => ['tag' => 'body'], 'data' => ['y' => 1000, 'vh' => 500, 'dh' => 1500]],
        ]);
    }

    public function test_heatmap_service_aggregates_bounded_markers_and_signals(): void
    {
        $this->seedMarkers();

        $result = app(HeatmapService::class)->aggregate($this->websiteId, null, null, '30d');

        $this->assertSame(4, $result['clicks']);
        $this->assertSame(2, $result['scrolls']);
        $this->assertSame(2, $result['sessions']);
        $this->assertSame(6, $result['sample_size']);
        $this->assertFalse($result['capped']);
        $this->assertSame(3, $result['points'][0]['count']);
        $this->assertSame(50.0, $result['points'][0]['x']);
        $this->assertSame(20.0, $result['points'][0]['y']);
        $this->assertSame('button#pay', $result['targets'][0]['label']);
        $this->assertSame(1, $result['scroll'][5]);
        $this->assertSame(1, $result['scroll'][10]);
        $this->assertSame('button#pay', $result['rage'][0]['label']);
        $this->assertSame(1, $result['rage'][0]['count']);
        $this->assertSame('a#next', $result['dead'][0]['label']);
        $this->assertSame('lwa_replay_markers, scoped to the active website and tenant.', $result['definitions']['source']);
    }

    public function test_heatmap_page_filters_and_scopes_results(): void
    {
        $this->seedMarkers();

        $this->get(route('lwa_heatmaps'))
            ->assertOk()
            ->assertSee('Heatmaps')
            ->assertSee('Click concentration')
            ->assertSee('Rage clicks')
            ->assertSee('Dead clicks')
            ->assertSee('button#pay');

        $checkout = app(HeatmapService::class)->aggregate($this->websiteId, null, 'checkout', '30d');
        $this->assertSame(3, $checkout['clicks']);
        $this->assertSame(1, $checkout['sessions']);

        $this->get(route('lwa_heatmaps', ['path' => 'checkout', 'range' => '7d']))
            ->assertOk()
            ->assertSee('value="checkout"', false);

        $other = Website::create([
            'name'        => 'Other',
            'domain'      => 'other-heatmap.test',
            'tracking_id' => 'trk_other_heatmap',
        ]);
        $foreign = app(HeatmapService::class)->aggregate((int) $other->id, null, null, '30d');
        $this->assertSame(0, $foreign['sample_size']);
        $this->assertSame(0, $foreign['clicks']);
    }

    public function test_heatmaps_are_gated_and_disabled_returns_404(): void
    {
        Gate::define('viewLwaReplay', fn ($user = null) => false);
        $this->get(route('lwa_heatmaps'))->assertStatus(403);

        Gate::define('viewLwaReplay', fn ($user = null) => true);
        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->get(route('lwa_heatmaps'))->assertNotFound();
    }
}
