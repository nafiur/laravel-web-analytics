<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class PathsApiTest extends TestCase
{
    use RefreshDatabase;

    private Website $website;
    private Website $otherWebsite;
    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'name' => 'Paths API', 'domain' => 'paths-api.test', 'tracking_id' => 'trk_papi',
        ]);
        $this->otherWebsite = Website::create([
            'name' => 'Other', 'domain' => 'paths-other.test', 'tracking_id' => 'trk_pother',
        ]);
        $this->today = CarbonImmutable::now()->startOfDay();

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->website->id);
    }

    public function test_paths_require_read_token_and_return_documented_sequences(): void
    {
        $this->getJson(route('lwa_api_insights_paths'))->assertStatus(401);

        $visitor = Visitor::create([
            'website_id' => $this->website->id, 'visitor_hash' => 'u_paths_api',
            'first_seen_at' => $this->today->subDays(10), 'last_seen_at' => $this->today,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->website->id, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $this->today->subDays(4 - $index)->addHours(2),
                'request_uuid' => 'paths-api-' . $index,
            ]);
        }

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_paths', [
                'start_event' => 'signup',
                'end_event' => 'purchase',
                'depth' => 4,
                'range' => '30d',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.sequenced_users', 1)
            ->assertJsonPath('data.summary.top_path_share', 100)
            ->assertJsonPath('data.paths.0.steps.0', 'signup')
            ->assertJsonPath('data.paths.0.steps.1', 'purchase')
            ->assertJsonCount(2, 'data.paths.0.steps');

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_paths', ['depth' => 99]))
            ->assertStatus(422);
    }

    public function test_paths_and_saved_configs_are_website_scoped(): void
    {
        $otherVisitor = Visitor::create([
            'website_id' => $this->otherWebsite->id, 'visitor_hash' => 'u_other_paths',
            'first_seen_at' => $this->today->subDay(), 'last_seen_at' => $this->today,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->otherWebsite->id, 'visitor_id' => $otherVisitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $this->today->subDays(2 - $index),
                'request_uuid' => 'paths-other-' . $index,
            ]);
        }

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);
        [, $admin] = ApiToken::issue((int) $this->website->id, 'admin', ['admin']);
        [, $otherRead] = ApiToken::issue((int) $this->otherWebsite->id, 'other-reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_paths'))
            ->assertOk()
            ->assertJsonPath('data.sample_size', 0)
            ->assertJsonPath('data.summary.paths_found', 0);

        $report = $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->postJson(route('lwa_api_insights_store'), [
                'name' => 'Signup paths',
                'config' => [
                    'type' => 'paths',
                    'start_event' => 'signup',
                    'end_event' => 'purchase',
                    'depth' => 3,
                    'limit' => 10,
                    'ignored' => 'removed',
                ],
            ])->assertStatus(201)->json('data');

        $this->assertSame([
            'type' => 'paths',
            'start_event' => 'signup',
            'end_event' => 'purchase',
            'depth' => 3,
            'limit' => 10,
        ], $report['config']);

        $this->withHeader('Authorization', 'Bearer ' . $otherRead)
            ->getJson(route('lwa_api_insights_show', $report['id']))
            ->assertStatus(404);
    }
}
