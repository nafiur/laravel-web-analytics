<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\PerformanceSpan;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\PerformanceService;
use Nafiur\WebAnalytics\Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private string $projectKey = 'trk_performance';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.observability.enabled', true);
        Gate::define('viewLwaObservability', fn ($user = null) => true);
        $website = Website::create([
            'name'        => 'Performance QA',
            'domain'      => 'performance.test',
            'tracking_id' => $this->projectKey,
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function spans(): array
    {
        return [
            'spans' => [
                [
                    'trace_id' => 'trace_1', 'span_id' => 'span_1', 'name' => 'GET /checkout',
                    'kind' => 'server', 'start_ms' => 0, 'duration_ms' => 120,
                    'status' => 'ok', 'attributes' => ['http.method' => 'GET', 'secret' => 'nope'],
                ],
                [
                    'trace_id' => 'trace_1', 'span_id' => 'span_2', 'parent_id' => 'span_1',
                    'name' => 'SELECT users', 'kind' => 'db', 'start_ms' => 10, 'duration_ms' => 480,
                    'status' => 'error', 'has_error' => true,
                ],
            ],
        ];
    }

    private function postSpans(array $payload)
    {
        return $this->withHeader('X-LWA-Project-Key', $this->projectKey)
            ->postJson(route('lwa_api_observability_spans'), $payload);
    }

    public function test_spans_are_stored_logged_deduplicated_and_aggregated(): void
    {
        $this->postSpans($this->spans())->assertStatus(202)->assertJsonPath('inserted', 2);
        $this->postSpans($this->spans())->assertStatus(202)->assertJsonPath('duplicates', 2);

        $this->assertSame(2, PerformanceSpan::count());
        $this->assertSame(2, AnalyticEvent::where('name', 'span')->count());
        $attributes = PerformanceSpan::where('span_id', 'span_1')->firstOrFail()->attributes;
        $this->assertArrayHasKey('http.method', $attributes);
        $this->assertArrayNotHasKey('secret', $attributes);

        $result = app(PerformanceService::class)->summarize($this->websiteId, null, '30d');
        $this->assertSame(2, $result['spans']);
        $this->assertSame(1, $result['traces']);
        $this->assertSame(50.0, $result['error_rate']);
        $this->assertSame('SELECT users', $result['operations'][0]['name']);

        $trace = app(PerformanceService::class)->trace('trace_1', $this->websiteId, null);
        $this->assertNotNull($trace);
        $this->assertCount(2, $trace['spans']);
    }

    public function test_performance_page_is_gated_and_disabled_returns_404(): void
    {
        Gate::define('viewLwaObservability', fn ($user = null) => false);
        $this->get(route('lwa_performance_traces'))->assertStatus(403);

        Gate::define('viewLwaObservability', fn ($user = null) => true);
        $this->postSpans($this->spans())->assertStatus(202);
        $this->get(route('lwa_performance_traces'))
            ->assertOk()
            ->assertSee('Performance & Traces')
            ->assertSee('GET /checkout')
            ->assertSee('Definitions');

        $this->get(route('lwa_performance_traces', ['trace' => 'trace_1']))
            ->assertOk()
            ->assertSee('Trace trace_1')
            ->assertSee('SELECT users');

        config()->set('laravel-web-analytics.observability.enabled', false);
        $this->get(route('lwa_performance_traces'))->assertNotFound();
        $this->postSpans($this->spans())->assertNotFound();
    }
}
