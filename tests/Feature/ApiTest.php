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

    public function test_api_event_batch_ingests_and_dedupes(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $writer] = ApiToken::issue($websiteId, 'batch-writer', ['write']);

        $payload = [
            'events' => [
                ['uuid' => 'batch-evt-1', 'name' => 'js_purchase', 'properties' => ['plan' => 'pro'], 'value' => 9.0, 'timestamp' => '2026-11-25T08:15:22Z'],
                ['uuid' => 'batch-evt-2', 'name' => 'js_signup', 'properties' => ['plan' => 'free'], 'value' => 0.0, 'timestamp' => '2026-11-25T09:15:22Z'],
            ],
        ];

        $this->withHeader('Authorization', 'Bearer ' . $writer)
            ->postJson(route('lwa_api_events_batch'), $payload)
            ->assertStatus(202)
            ->assertJsonPath('success', true);

        $this->assertSame(2, AnalyticEvent::where('website_id', $websiteId)->count());

        // Retry with the same uuids must not create duplicate rows.
        $this->withHeader('Authorization', 'Bearer ' . $writer)
            ->postJson(route('lwa_api_events_batch'), $payload)
            ->assertStatus(202);

        $this->assertSame(2, AnalyticEvent::where('website_id', $websiteId)->count());
    }

    public function test_api_event_batch_rejects_malformed_events(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $writer] = ApiToken::issue($websiteId, 'batch-writer', ['write']);

        $this->withHeader('Authorization', 'Bearer ' . $writer)
            ->postJson(route('lwa_api_events_batch'), ['events' => [['properties' => ['plan' => 'pro']]]])
            ->assertStatus(422);

        $this->assertSame(0, AnalyticEvent::count());
    }

    public function test_api_dashboard_crud_respects_token_abilities(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [, $read] = ApiToken::issue($websiteId, 'dash-read', ['read']);
        [, $admin] = ApiToken::issue($websiteId, 'dash-admin', ['admin']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_dashboards'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->postJson(route('lwa_api_dashboards_store'), ['name' => 'Denied'])
            ->assertStatus(403);

        $created = $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->postJson(route('lwa_api_dashboards_store'), [
                'name'     => 'Traffic board',
                'template' => 'traffic',
            ])
            ->assertCreated();

        $id = $created->json('data.id');
        $this->assertNotEmpty($created->json('data.widgets'));

        $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->putJson(route('lwa_api_dashboards_update', $id), [
                'widgets' => [['widget' => 'chart_traffic', 'w' => 12, 'h' => 1, 'title' => '']],
            ])
            ->assertOk()
            ->assertJsonPath('data.widgets.0.widget', 'chart_traffic');

        $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->deleteJson(route('lwa_api_dashboards_destroy', $id))
            ->assertOk();
    }

    public function test_token_can_be_rotated_and_old_token_stops_working(): void
    {
        $websiteId = (int) config('laravel-web-analytics.analytics.website_id');
        [$token, $oldPlain] = ApiToken::issue($websiteId, 'rotating', ['read']);
        $newPlain = $token->rotate();

        $this->assertNotSame($oldPlain, $newPlain);
        $this->assertSame(ApiToken::hash($newPlain), $token->fresh()->token_hash);

        $this->withHeader('Authorization', 'Bearer ' . $oldPlain)
            ->getJson(route('lwa_api_summary'))->assertStatus(401);

        $this->withHeader('Authorization', 'Bearer ' . $newPlain)
            ->getJson(route('lwa_api_summary'))->assertOk();

        $token->revoke();
        $this->withHeader('Authorization', 'Bearer ' . $newPlain)
            ->getJson(route('lwa_api_summary'))->assertStatus(401);
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
