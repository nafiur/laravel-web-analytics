<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class PathsPageTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Paths UI', 'domain' => 'paths-ui.test', 'tracking_id' => 'trk_pui',
        ]);
        $this->websiteId = (int) $website->id;
        $this->today = CarbonImmutable::now()->startOfDay();

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_paths_page_renders_paths_positions_and_definitions(): void
    {
        $visitor = Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => 'u_ui_paths',
            'first_seen_at' => $this->today->subDays(10), 'last_seen_at' => $this->today,
        ]);
        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->websiteId, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $this->today->subDays(4 - $index)->addHours(2),
                'request_uuid' => 'paths-ui-' . $index,
            ]);
        }

        $this->get(route('lwa_paths'))
            ->assertOk()
            ->assertSee('User Paths')
            ->assertSee('Top paths')
            ->assertSee('Events by position')
            ->assertSee('Metric definitions')
            ->assertSee('Sequenced users');

        $this->get(route('lwa_paths', [
            'start_event' => 'signup',
            'end_event' => 'purchase',
            'depth' => 3,
            'range' => '30d',
        ]))
            ->assertOk()
            ->assertSee('signup')
            ->assertSee('purchase')
            ->assertSee('Step 1');

        $this->get(route('lwa_paths', ['depth' => 99]))->assertStatus(302);
    }

    public function test_path_report_can_be_saved_and_deleted_from_the_page(): void
    {
        $config = [
            'type' => 'paths',
            'start_event' => 'signup',
            'end_event' => 'purchase',
            'depth' => 3,
            'limit' => 10,
            'range' => '7d',
        ];

        $this->post(route('lwa_paths_save'), [
            'name' => 'Signup paths',
            'config' => json_encode($config),
        ])->assertRedirect();

        $this->get(route('lwa_paths'))
            ->assertOk()
            ->assertSee('Signup paths');

        foreach (['lwa_trends', 'lwa_funnels', 'lwa_retention'] as $other) {
            $this->get(route($other))
                ->assertOk()
                ->assertDontSee('Signup paths');
        }

        $report = Insight::firstOrFail();
        $this->assertSame('paths', $report->config['type']);
        $this->assertSame('signup', $report->config['start_event']);

        $this->post(route('lwa_paths_destroy', $report->id))->assertRedirect();

        $this->assertDatabaseMissing('lwa_insights', ['id' => $report->id]);
    }
}
