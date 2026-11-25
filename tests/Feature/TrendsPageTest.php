<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class TrendsPageTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Trends UI', 'domain' => 'trends.test', 'tracking_id' => 'trk_trends',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_trends_page_renders_chart_data_and_definitions(): void
    {
        $visitor = Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => 'f_ui',
            'first_seen_at' => CarbonImmutable::now(), 'last_seen_at' => CarbonImmutable::now(),
        ]);
        AnalyticEvent::create([
            'website_id' => $this->websiteId, 'visitor_id' => $visitor->id,
            'name' => 'signup', 'properties' => ['plan' => 'pro'], 'value' => 10,
            'created_at' => CarbonImmutable::now(), 'request_uuid' => 'ui-trend-1',
        ]);

        $this->get(route('lwa_trends'))
            ->assertOk()
            ->assertSee('Trends')
            ->assertSee('chartTrends')
            ->assertSee('Metric definitions')
            ->assertSee('signup')
            ->assertSee('Events scanned');

        $this->get(route('lwa_trends', ['metric' => 'value', 'event' => 'signup']))
            ->assertOk()
            ->assertSee('Total value');

        $this->get(route('lwa_trends', ['metric' => 'nope']))->assertStatus(302);
    }

    public function test_report_can_be_saved_and_deleted_from_the_page(): void
    {
        $this->post(route('lwa_trends_save'), [
            'name' => 'Signup trends',
            'config' => json_encode(['event' => 'signup', 'metric' => 'users']),
        ])->assertRedirect();

        $this->get(route('lwa_trends'))
            ->assertOk()
            ->assertSee('Signup trends');

        $id = \Nafiur\WebAnalytics\Models\Analytics\Insight::firstOrFail()->id;

        $this->post(route('lwa_trends_destroy', $id))->assertRedirect();

        $this->assertDatabaseMissing('lwa_insights', ['id' => $id]);
    }
}
