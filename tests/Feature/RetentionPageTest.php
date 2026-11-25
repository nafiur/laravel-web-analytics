<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class RetentionPageTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Retention UI', 'domain' => 'retention-ui.test', 'tracking_id' => 'trk_rui',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_retention_page_renders_cohorts_lifecycle_and_definitions(): void
    {
        $today = CarbonImmutable::now()->startOfDay();
        $visitor = Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => 'u_ui_retention',
            'first_seen_at' => $today->subDays(10), 'last_seen_at' => $today,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->websiteId, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $today->subDays(4 - $index)->addHours(2),
                'request_uuid' => 'retention-ui-' . $index,
            ]);
        }

        $this->get(route('lwa_retention'))
            ->assertOk()
            ->assertSee('Retention')
            ->assertSee('Cohort retention')
            ->assertSee('Lifecycle by period')
            ->assertSee('Metric definitions')
            ->assertSee('Period 1 retention');

        $this->get(route('lwa_retention', [
            'event' => 'signup',
            'granularity' => 'week',
            'periods' => 4,
            'range' => '30d',
        ]))
            ->assertOk()
            ->assertSee('signup')
            ->assertSee('Weekly cohorts')
            ->assertSee('W0')
            ->assertSee('W1');

        $this->get(route('lwa_retention', ['periods' => 99]))->assertStatus(302);
    }

    public function test_retention_report_can_be_saved_and_deleted_from_the_page(): void
    {
        $config = [
            'type' => 'retention',
            'event' => 'signup',
            'granularity' => 'week',
            'periods' => 4,
            'range' => '30d',
        ];

        $this->post(route('lwa_retention_save'), [
            'name' => 'Weekly signup retention',
            'config' => json_encode($config),
        ])->assertRedirect();

        $this->get(route('lwa_retention'))
            ->assertOk()
            ->assertSee('Weekly signup retention');

        $this->get(route('lwa_trends'))
            ->assertOk()
            ->assertDontSee('Weekly signup retention');
        $this->get(route('lwa_funnels'))
            ->assertOk()
            ->assertDontSee('Weekly signup retention');

        $report = Insight::firstOrFail();
        $this->assertSame('retention', $report->config['type']);
        $this->assertSame('week', $report->config['granularity']);

        $this->post(route('lwa_retention_destroy', $report->id))->assertRedirect();

        $this->assertDatabaseMissing('lwa_insights', ['id' => $report->id]);
    }
}
