<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Pageview;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'API Test', 'domain' => 'api.test', 'tracking_id' => 'trk_api',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $website->id);
    }

    public function test_facade_tracks_pageviews_and_events(): void
    {
        Analytics::pageview('/from-facade', ['visitor_hash' => 'facade-visitor']);
        Analytics::event('signup', ['plan' => 'pro', 'password' => 'strip-me'], 99.0);
        Analytics::goal('registration');

        $this->assertSame(1, Pageview::count());
        $this->assertSame(2, AnalyticEvent::count());

        $signup = AnalyticEvent::where('name', 'signup')->first();
        $this->assertSame(['plan' => 'pro'], $signup->properties);
        $this->assertSame(99.0, $signup->value);
        $this->assertNotNull(AnalyticEvent::where('name', 'goal_registration')->first());
    }

    public function test_facade_summary_returns_aggregate_shape(): void
    {
        $summary = Analytics::summary('24h');

        $this->assertArrayHasKey('total_requests', $summary);
        $this->assertArrayHasKey('bounce_rate', $summary);
        $this->assertSame('Aggregated analytics', $summary['storage_type']);
    }

    public function test_api_requires_a_token(): void
    {
        $this->getJson(route('lwa_api_summary'))->assertStatus(401);
    }

    public function test_api_summary_works_with_bearer_token(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [$token, $plain] = ApiToken::issue($websiteId, 'test', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson(route('lwa_api_summary'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($token->fresh()->last_used_at);
    }

    public function test_api_event_requires_write_ability(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $readOnly] = ApiToken::issue($websiteId, 'read-only', ['read']);
        [, $writer] = ApiToken::issue($websiteId, 'writer', ['write']);

        $this->withHeader('Authorization', 'Bearer ' . $readOnly)
            ->postJson(route('lwa_api_event'), ['name' => 'denied'])
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer ' . $writer)
            ->postJson(route('lwa_api_event'), ['name' => 'allowed', 'properties' => ['key' => 'value']])
            ->assertStatus(202);

        $this->assertSame(1, AnalyticEvent::where('name', 'allowed')->count());
    }

    public function test_api_is_rate_limited(): void
    {
        config()->set('laravel-web-analytics.api.rate_limit_per_minute', 2);

        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $plain] = ApiToken::issue($websiteId, 'limited', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson(route('lwa_api_summary'))->assertOk();
        $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson(route('lwa_api_summary'))->assertOk();
        $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson(route('lwa_api_summary'))->assertStatus(429);
    }

    public function test_api_websites_requires_admin_ability(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $admin] = ApiToken::issue($websiteId, 'admin', ['admin']);

        $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->getJson(route('lwa_api_websites'))
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
