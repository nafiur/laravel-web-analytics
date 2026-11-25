<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class FunnelApiTest extends TestCase
{
    use RefreshDatabase;

    private Website $website;
    private Website $otherWebsite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'name' => 'Funnel API', 'domain' => 'funnel-api.test', 'tracking_id' => 'trk_fapi',
        ]);
        $this->otherWebsite = Website::create([
            'name' => 'Other', 'domain' => 'funnel-other.test', 'tracking_id' => 'trk_fother',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->website->id);
    }

    public function test_funnel_requires_read_token_and_returns_documented_steps(): void
    {
        $this->getJson(route('lwa_api_insights_funnel', [
            'steps' => ['view_item', 'purchase'],
        ]))->assertStatus(401);

        $now = CarbonImmutable::now();
        $visitor = Visitor::create([
            'website_id' => $this->website->id, 'visitor_hash' => 'u_funnel_api',
            'first_seen_at' => $now->subDay(), 'last_seen_at' => $now,
        ]);
        foreach (['view_item', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->website->id, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $now->subMinutes(10 - $index),
                'request_uuid' => 'funnel-api-' . $index,
            ]);
        }

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_funnel', [
                'steps' => ['view_item', 'purchase'],
                'window' => '24h',
                'range' => '24h',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.completed_users', 1)
            ->assertJsonPath('data.overall_conversion_rate', 100)
            ->assertJsonPath('data.window', '24h')
            ->assertJsonCount(2, 'data.steps');

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_funnel', ['steps' => ['only-one-step']]))
            ->assertStatus(422);
    }

    public function test_funnel_query_and_saved_configs_are_website_scoped(): void
    {
        $now = CarbonImmutable::now();
        $otherVisitor = Visitor::create([
            'website_id' => $this->otherWebsite->id, 'visitor_hash' => 'u_other',
            'first_seen_at' => $now->subDay(), 'last_seen_at' => $now,
        ]);
        foreach (['view_item', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->otherWebsite->id, 'visitor_id' => $otherVisitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $now->subMinutes(10 - $index),
                'request_uuid' => 'funnel-other-' . $index,
            ]);
        }

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);
        [, $admin] = ApiToken::issue((int) $this->website->id, 'admin', ['admin']);
        [, $otherRead] = ApiToken::issue((int) $this->otherWebsite->id, 'other-reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_funnel', ['steps' => ['view_item', 'purchase']]))
            ->assertOk()
            ->assertJsonPath('data.sample_size', 0)
            ->assertJsonPath('data.completed_users', 0);

        $report = $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->postJson(route('lwa_api_insights_store'), [
                'name' => 'Checkout funnel',
                'config' => [
                    'type' => 'funnel',
                    'steps' => ['view_item', 'purchase'],
                    'window' => '7d',
                    'ignored' => 'removed',
                ],
            ])->assertStatus(201)->json('data');

        $this->assertSame([
            'type' => 'funnel',
            'steps' => ['view_item', 'purchase'],
            'window' => '7d',
        ], $report['config']);

        $this->withHeader('Authorization', 'Bearer ' . $otherRead)
            ->getJson(route('lwa_api_insights_show', $report['id']))
            ->assertStatus(404);
    }
}
