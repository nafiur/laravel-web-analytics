<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class RetentionApiTest extends TestCase
{
    use RefreshDatabase;

    private Website $website;
    private Website $otherWebsite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'name' => 'Retention API', 'domain' => 'retention-api.test', 'tracking_id' => 'trk_rapi',
        ]);
        $this->otherWebsite = Website::create([
            'name' => 'Other', 'domain' => 'retention-other.test', 'tracking_id' => 'trk_rother',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->website->id);
    }

    public function test_retention_requires_read_token_and_returns_documented_cohorts(): void
    {
        $this->getJson(route('lwa_api_insights_retention'))->assertStatus(401);

        $today = CarbonImmutable::now()->startOfDay();
        $visitor = Visitor::create([
            'website_id' => $this->website->id, 'visitor_hash' => 'u_retention_api',
            'first_seen_at' => $today->subDays(10), 'last_seen_at' => $today,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->website->id, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $today->subDays(4 - $index)->addHours(2),
                'request_uuid' => 'retention-api-' . $index,
            ]);
        }

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_retention', [
                'granularity' => 'day',
                'range' => '30d',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.cohorts', 1)
            ->assertJsonPath('data.summary.cohorted_users', 1)
            ->assertJsonPath('data.summary.period_one_rate', 100)
            ->assertJsonPath('data.granularity', 'day')
            ->assertJsonCount(1, 'data.cohorts');

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_retention', ['periods' => 99]))
            ->assertStatus(422);
    }

    public function test_retention_and_saved_configs_are_website_scoped(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $otherVisitor = Visitor::create([
            'website_id' => $this->otherWebsite->id, 'visitor_hash' => 'u_other_retention',
            'first_seen_at' => $today->subDay(), 'last_seen_at' => $today,
        ]);
        AnalyticEvent::create([
            'website_id' => $this->otherWebsite->id, 'visitor_id' => $otherVisitor->id,
            'name' => 'signup', 'properties' => [], 'value' => null,
            'created_at' => $today->subDay(), 'request_uuid' => 'retention-other',
        ]);

        [, $read] = ApiToken::issue((int) $this->website->id, 'reader', ['read']);
        [, $admin] = ApiToken::issue((int) $this->website->id, 'admin', ['admin']);
        [, $otherRead] = ApiToken::issue((int) $this->otherWebsite->id, 'other-reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $read)
            ->getJson(route('lwa_api_insights_retention'))
            ->assertOk()
            ->assertJsonPath('data.sample_size', 0)
            ->assertJsonPath('data.summary.cohorts', 0);

        $report = $this->withHeader('Authorization', 'Bearer ' . $admin)
            ->postJson(route('lwa_api_insights_store'), [
                'name' => 'Signup retention',
                'config' => [
                    'type' => 'retention',
                    'event' => 'signup',
                    'granularity' => 'week',
                    'periods' => 4,
                    'ignored' => 'removed',
                ],
            ])->assertStatus(201)->json('data');

        $this->assertSame([
            'type' => 'retention',
            'event' => 'signup',
            'granularity' => 'week',
            'periods' => 4,
        ], $report['config']);

        $this->withHeader('Authorization', 'Bearer ' . $otherRead)
            ->getJson(route('lwa_api_insights_show', $report['id']))
            ->assertStatus(404);
    }
}
