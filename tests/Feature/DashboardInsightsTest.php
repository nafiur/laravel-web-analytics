<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Dashboard;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\DashboardInsights;
use Nafiur\WebAnalytics\Tests\TestCase;

class DashboardInsightsTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Dashboard insights', 'domain' => 'dash-insights.test',
            'tracking_id' => 'trk_dashinsights',
        ]);
        $this->websiteId = (int) $website->id;
        $this->today = CarbonImmutable::now()->startOfDay();

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    /** @param array<string, mixed> $config */
    private function insight(string $name, array $config): Insight
    {
        return Insight::create([
            'name' => $name,
            'config' => $config,
            'website_id' => $this->websiteId,
        ]);
    }

    /** @param array<int, array<string, mixed>> $widgets */
    private function dashboard(array $widgets): Dashboard
    {
        return Dashboard::create([
            'name' => 'Insight board',
            'widgets' => $widgets,
            'settings' => ['range' => '30d'],
            'website_id' => $this->websiteId,
        ]);
    }

    private function seedEvents(): void
    {
        $visitor = Visitor::create([
            'website_id' => $this->websiteId, 'visitor_hash' => 'dash_insight_user',
            'first_seen_at' => $this->today->subDays(10), 'last_seen_at' => $this->today,
        ]);

        foreach (['signup', 'purchase'] as $index => $name) {
            AnalyticEvent::create([
                'website_id' => $this->websiteId, 'visitor_id' => $visitor->id,
                'name' => $name, 'properties' => [], 'value' => null,
                'created_at' => $this->today->subDays(4 - $index)->addHours(2),
                'request_uuid' => 'dash-insight-' . $index,
            ]);
        }
    }

    public function test_saved_insight_widgets_render_from_the_documented_engines(): void
    {
        $this->seedEvents();

        $trend = $this->insight('Signup trend', [
            'type' => 'trends', 'event' => 'signup', 'metric' => 'events',
            'granularity' => 'day', 'range' => '30d',
        ]);
        $funnel = $this->insight('Signup funnel', [
            'type' => 'funnel', 'steps' => ['signup', 'purchase'],
            'window' => '7d', 'range' => '30d',
        ]);
        $retention = $this->insight('Signup retention', [
            'type' => 'retention', 'event' => 'signup',
            'granularity' => 'day', 'periods' => 4, 'range' => '30d',
        ]);
        $paths = $this->insight('Signup paths', [
            'type' => 'paths', 'start_event' => 'signup',
            'depth' => 3, 'range' => '30d',
        ]);

        $widgets = [];
        foreach ([$trend, $funnel, $retention, $paths] as $report) {
            $widgets[] = [
                'widget' => 'insight:' . $report->id,
                'type' => 'insight',
                'insight_id' => $report->id,
                'w' => 12, 'h' => 1, 'title' => $report->name,
            ];
        }
        // Legacy layouts store only the catalog id; metadata must be resolved.
        $widgets[] = ['widget' => 'chart_traffic', 'w' => 6, 'h' => 1, 'title' => ''];
        $board = $this->dashboard($widgets);

        $this->get(route('lwa_dashboard_view', $board->id))
            ->assertOk()
            ->assertSee('Signup trend', false)
            ->assertSee('Signup funnel', false)
            ->assertSee('Signup retention', false)
            ->assertSee('Signup paths', false)
            ->assertSee('data-widget-type="insight"', false)
            ->assertSee('Overall conversion · 1 of 1 users', false)
            ->assertSee('Period 1 retention', false)
            ->assertSee('Top path share', false)
            ->assertSee('Source: lwa_events', false)
            ->assertSee('Open report', false)
            ->assertSee('data-widget-chart="dw4"', false);

        $this->get(route('lwa_dashboard_edit', $board->id))
            ->assertOk()
            ->assertSee('Saved insights', false)
            ->assertSee('data-insight-add="' . $trend->id . '"', false)
            ->assertSee('id="lwaDashboardCatalog"', false)
            ->assertSee('"type":"chart","key":"requests"', false)
            ->assertSee('id="lwaDashboardInsights"', false);
    }

    public function test_insight_widgets_are_website_scoped_and_expose_missing_reports(): void
    {
        $other = Website::create([
            'name' => 'Other', 'domain' => 'other-dash.test', 'tracking_id' => 'trk_otherdash',
        ]);
        $foreign = Insight::create([
            'name' => 'Foreign report',
            'config' => ['type' => 'trends', 'event' => 'signup'],
            'website_id' => $other->id,
        ]);

        $board = $this->dashboard([
            [
                'widget' => 'insight:' . $foreign->id,
                'type' => 'insight',
                'insight_id' => $foreign->id,
                'w' => 12, 'h' => 1, 'title' => 'Foreign report',
            ],
            [
                'widget' => 'insight:999999',
                'type' => 'insight',
                'insight_id' => 999999,
                'w' => 12, 'h' => 1, 'title' => 'Deleted report',
            ],
            [
                'widget' => 'insight:broken',
                'type' => 'insight',
                'w' => 12, 'h' => 1, 'title' => 'Broken widget',
            ],
        ]);

        $content = $this->get(route('lwa_dashboard_view', $board->id))
            ->assertOk()
            ->assertSee('Saved report unavailable', false)
            ->assertSee('This widget has no saved report id.', false)
            ->assertDontSee('Foreign report ·', false)
            ->getContent();

        $this->assertSame(2, substr_count($content, 'Saved report unavailable'));

        $service = app(DashboardInsights::class);
        $this->assertSame([], $service->reports($this->websiteId, null));
    }

    public function test_only_the_first_twelve_insight_widgets_are_computed(): void
    {
        $report = $this->insight('Capped report', [
            'type' => 'trends', 'event' => 'signup', 'range' => '30d',
        ]);

        $widgets = [];
        for ($i = 0; $i < DashboardInsights::MAX_WIDGETS + 2; $i++) {
            $widgets[] = [
                'widget' => 'insight:' . $report->id,
                'type' => 'insight',
                'insight_id' => $report->id,
                'w' => 12, 'h' => 1, 'title' => 'Capped report',
            ];
        }
        $board = $this->dashboard($widgets);

        $content = $this->get(route('lwa_dashboard_view', $board->id))
            ->assertOk()
            ->assertSee('Insight widget limit reached: only the first 12 insight widgets are computed.', false)
            ->getContent();

        $this->assertSame(DashboardInsights::MAX_WIDGETS, substr_count($content, 'Source: lwa_events'));
    }

    public function test_insight_widgets_use_the_dashboard_time_window(): void
    {
        $this->seedEvents();
        $report = $this->insight('Windowed report', [
            'type' => 'trends', 'event' => 'signup', 'range' => '30d',
        ]);
        $widgets = [[
            'widget' => 'insight:' . $report->id,
            'type' => 'insight',
            'insight_id' => $report->id,
            'w' => 12, 'h' => 1, 'title' => $report->name,
        ]];

        $service = app(DashboardInsights::class);
        $wide = $service->results($this->websiteId, null, $widgets, '30d');
        $narrow = $service->results($this->websiteId, null, $widgets, '1h');
        $customStart = $this->today->subDays(5)->toDateString();
        $customEnd = $this->today->subDays(3)->toDateString();
        $custom = $service->results($this->websiteId, null, $widgets, 'custom', $customStart, $customEnd);

        $this->assertSame(1, $wide[0]['sample_size']);
        $this->assertSame(0, $narrow[0]['sample_size']);
        $this->assertSame(1, $custom[0]['sample_size']);
    }

    public function test_insight_widget_layouts_persist_through_web_and_api_crud(): void
    {
        $report = $this->insight('Saved trend', [
            'type' => 'trends', 'event' => 'signup', 'range' => '30d',
        ]);
        $widget = [
            'widget' => 'insight:' . $report->id,
            'type' => 'insight',
            'insight_id' => $report->id,
            'w' => 12, 'h' => 1, 'title' => 'Saved trend',
        ];

        $created = $this->postJson(route('lwa_dashboards_store'), [
            'name'    => 'Widget board',
            'widgets' => [$widget],
        ])->assertCreated();
        $id = $created->json('id');

        $this->assertSame('insight:' . $report->id, Dashboard::findOrFail($id)->widgets[0]['widget']);
        $this->get(route('lwa_dashboard_view', $id))
            ->assertOk()
            ->assertSee('Saved trend', false)
            ->assertSee('data-widget-type="insight"', false);

        $widget['title'] = 'Renamed trend';
        $this->putJson(route('lwa_dashboard_update', $id), ['widgets' => [$widget]])
            ->assertOk()
            ->assertJsonPath('dashboard.widgets.0.insight_id', $report->id)
            ->assertJsonPath('dashboard.widgets.0.title', 'Renamed trend');

        [, $plain] = ApiToken::issue($this->websiteId, 'widget-admin', ['read', 'admin']);
        $api = $this->withHeader('Authorization', 'Bearer ' . $plain);

        $api->getJson(route('lwa_api_dashboards_show', $id))
            ->assertOk()
            ->assertJsonPath('data.widgets.0.title', 'Renamed trend');

        $api->putJson(route('lwa_api_dashboards_update', $id), ['widgets' => []])
            ->assertOk()
            ->assertJsonCount(0, 'data.widgets');

        $api->deleteJson(route('lwa_api_dashboards_destroy', $id))->assertOk();
        $this->assertNull(Dashboard::find($id));
    }
}
