<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class FunnelsPageTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Funnels UI', 'domain' => 'funnels-ui.test', 'tracking_id' => 'trk_fui',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_funnels_page_renders_steps_rates_and_definitions(): void
    {
        $now = CarbonImmutable::now();
        $visitor = Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => 'u_ui_funnel',
            'first_seen_at' => $now->subDay(), 'last_seen_at' => $now,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->websiteId, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $now->subMinutes(20 - $index),
                'request_uuid' => 'funnel-ui-' . $index,
            ]);
        }

        $this->get(route('lwa_funnels'))
            ->assertOk()
            ->assertSee('Funnels')
            ->assertSee('Funnel breakdown')
            ->assertSee('Metric definitions')
            ->assertSee('view_item')
            ->assertSee('Entered')
            ->assertSee('Overall conversion');

        $this->get(route('lwa_funnels', [
            'steps' => ['signup', 'purchase'],
            'window' => '24h',
        ]))
            ->assertOk()
            ->assertSee('signup')
            ->assertSee('100.00%')
            ->assertSee('24h');

        $this->get(route('lwa_funnels', ['steps' => ['only-one-step']]))->assertStatus(302);
    }

    public function test_funnel_report_can_be_saved_and_deleted_from_the_page(): void
    {
        $config = [
            'type' => 'funnel',
            'steps' => ['signup', 'purchase'],
            'window' => '7d',
            'range' => '7d',
        ];

        $this->post(route('lwa_funnels_save'), [
            'name' => 'Signup to purchase',
            'config' => json_encode($config),
        ])->assertRedirect();

        $this->get(route('lwa_funnels'))
            ->assertOk()
            ->assertSee('Signup to purchase');

        $this->get(route('lwa_trends'))
            ->assertOk()
            ->assertDontSee('Signup to purchase');

        $report = Insight::firstOrFail();
        $this->assertSame('funnel', $report->config['type']);
        $this->assertSame(['signup', 'purchase'], $report->config['steps']);

        $this->post(route('lwa_funnels_destroy', $report->id))->assertRedirect();

        $this->assertDatabaseMissing('lwa_insights', ['id' => $report->id]);
    }
}
