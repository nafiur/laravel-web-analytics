<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Models\Analytics\Pageview;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class AnalyticsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_tables_are_created_when_enabled(): void
    {
        foreach ([
            'lwa_websites', 'lwa_visitors', 'lwa_sessions', 'lwa_pageviews',
            'lwa_events', 'lwa_hourly_stats', 'lwa_daily_stats', 'lwa_upgrade_checkpoints',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }
    }

    public function test_website_scope_is_applied_to_analytics_models(): void
    {
        Website::create([
            'name' => 'First', 'domain' => 'first.test', 'tracking_id' => 'trk_first',
        ]);
        Website::create([
            'name' => 'Second', 'domain' => 'second.test', 'tracking_id' => 'trk_second',
        ]);

        $firstId = Website::where('tracking_id', 'trk_first')->value('id');
        $secondId = Website::where('tracking_id', 'trk_second')->value('id');

        Pageview::create([
            'website_id' => $firstId, 'session_id' => 1, 'visitor_id' => 1,
            'path' => '/first', 'created_at' => now(),
        ]);
        Pageview::create([
            'website_id' => $secondId, 'session_id' => 2, 'visitor_id' => 2,
            'path' => '/second', 'created_at' => now(),
        ]);

        config()->set('laravel-web-analytics.analytics.website_id', $firstId);

        $this->assertSame(1, Pageview::count());
        $this->assertSame('/first', Pageview::first()->path);
    }
}
