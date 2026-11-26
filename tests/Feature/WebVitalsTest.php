<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\WebVitalsService;
use Nafiur\WebAnalytics\Tests\TestCase;

class WebVitalsTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.web_vitals.enabled', true);
        $website = Website::create([
            'name'        => 'Vitals QA',
            'domain'      => 'vitals.test',
            'tracking_id' => 'trk_vitals',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function vital(string $metric, float $value, string $path = '/'): void
    {
        $now = CarbonImmutable::now();
        app(EventStore::class)->insert([
            new UnifiedEvent(
                uuid: (string) Str::uuid(),
                name: 'web_vital',
                timestamp: $now,
                distinctId: null,
                anonymousId: 'vitals-qa',
                sessionId: null,
                websiteId: $this->websiteId,
                properties: ['metric' => $metric, 'value' => $value, 'path' => $path],
                context: [],
                value: null,
                createdAt: $now,
            ),
        ]);
    }

    private function seedVitals(): void
    {
        foreach ([2000, 3000, 5000] as $value) {
            $this->vital('lcp', $value);
        }
        foreach ([0.05, 0.3] as $value) {
            $this->vital('cls', $value);
        }
        foreach ([150, 600] as $value) {
            $this->vital('inp', $value);
        }
        $this->vital('lcp', 4000, '/checkout');
        $this->vital('inp', 400, '/checkout');
    }

    public function test_web_vitals_service_computes_p75_buckets_and_paths(): void
    {
        $this->seedVitals();

        $result = app(WebVitalsService::class)->summarize($this->websiteId, '30d');

        $this->assertSame(9, $result['sample_size']);
        $this->assertFalse($result['capped']);
        $this->assertSame(4000.0, $result['metrics']['lcp']['p75']);
        $this->assertSame(1, $result['metrics']['lcp']['good']);
        $this->assertSame(1, $result['metrics']['lcp']['poor']);
        $this->assertSame(0.3, $result['metrics']['cls']['p75']);
        $this->assertSame(600.0, $result['metrics']['inp']['p75']);
        $this->assertCount(2, $result['paths']);
        $this->assertContains('/', array_column($result['paths'], 'path'));
        $this->assertContains('/checkout', array_column($result['paths'], 'path'));

        $filtered = app(WebVitalsService::class)->summarize($this->websiteId, '30d', '/checkout');
        $this->assertSame(1, $filtered['metrics']['lcp']['count']);
        $this->assertSame(4000.0, $filtered['metrics']['lcp']['p75']);
        $this->assertSame('lwa_events rows named web_vital, emitted by the browser SDK when web vitals are enabled.', $result['definitions']['source']);
    }

    public function test_web_vitals_page_renders_filters_and_definitions(): void
    {
        $this->seedVitals();

        $this->get(route('lwa_web_vitals'))
            ->assertOk()
            ->assertSee('Web Vitals')
            ->assertSee('Largest Contentful Paint')
            ->assertSee('Interaction to Next Paint')
            ->assertSee('Definitions')
            ->assertSee('4,000.000');

        $this->get(route('lwa_web_vitals', ['path' => '/checkout', 'range' => '7d']))
            ->assertOk()
            ->assertSee('value="/checkout"', false)
            ->assertSee('value="7d"', false);
    }

    public function test_web_vitals_page_is_hidden_when_disabled(): void
    {
        config()->set('laravel-web-analytics.web_vitals.enabled', false);
        $this->get(route('lwa_web_vitals'))->assertNotFound();
    }

    public function test_sdk_bundle_emits_inp_and_path_for_web_vitals(): void
    {
        config()->set('laravel-web-analytics.sdk.enabled', true);
        config()->set('laravel-web-analytics.sdk.web_vitals', true);

        $this->get(route('lwa_sdk'))
            ->assertOk()
            ->assertSee('"web_vitals":true', false)
            ->assertSee("report('inp'", false)
            ->assertSee('path: global.location ? global.location.pathname', false);
    }
}
