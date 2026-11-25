<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class InsightsApiTest extends TestCase
{
    use RefreshDatabase;

    private Website $website;
    private Website $otherWebsite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'name' => 'Insights API', 'domain' => 'insights-api.test', 'tracking_id' => 'trk_iapi',
        ]);
        $this->otherWebsite = Website::create([
            'name' => 'Other', 'domain' => 'other.test', 'tracking_id' => 'trk_other',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->website->id);
    }

    public function test_trend_requires_read_token_and_returns_documented_series(): void
    {
        $this->getJson(route('lwa_api_insights_trend'))->assertStatus(401);

        $visitor = Visitor::create([
            'website_id' => $this->website->id, 'visitor_hash' => 'f_api',
            'first_seen_at' => CarbonImmutable::now(), 'last_seen_at' => CarbonImmutable::now(),
        ]);
        AnalyticEvent::create([
            'website_id' => $this->website->id, 'visitor_id' => $visitor->id,
            'name' => 'purchase', 'properties' => ['plan' => 'pro'], 'value' => 25,
            'created_at' => CarbonImmutable::now(), 'request_uuid' => 'api-trend-1',
        ]);

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_trend', ['event' => 'purchase', 'metric' => 'value']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.global_total', 25)
            ->assertJsonPath('data.sample_size', 1)
            ->assertJsonPath('filters.metric', 'value');

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_trend', ['metric' => 'invalid']))
            ->assertStatus(422);
    }

    public function test_saved_reports_crud_and_website_isolation(): void
    {
        [, $admin] = ApiToken::issue((int) $this->website->id, 'admin', ['admin']);
        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);
        [, $otherAdmin] = ApiToken::issue((int) $this->otherWebsite->id, 'other-reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->postJson(route('lwa_api_insights_store'), [
                'name' => 'Denied', 'config' => ['metric' => 'events'],
            ])->assertStatus(403);

        $created = $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->postJson(route('lwa_api_insights_store'), [
                'name' => 'Weekly signups',
                'config' => ['metric' => 'users', 'event' => 'signup', 'range' => '7d', 'ignored' => 'x'],
            ])->assertStatus(201)->json('data');

        $this->assertSame(['metric' => 'users', 'event' => 'signup', 'range' => '7d'], $created['config']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights'))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_show', $created['id']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Weekly signups');

        $this->withHeader('Authorization', 'Bearer ' . $otherAdmin)
            ->getJson(route('lwa_api_insights_show', $created['id']))
            ->assertStatus(404);

        $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->putJson(route('lwa_api_insights_update', $created['id']), ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->deleteJson(route('lwa_api_insights_destroy', $created['id']))
            ->assertOk();

        $this->assertDatabaseMissing('lwa_insights', ['id' => $created['id']]);
    }
}
