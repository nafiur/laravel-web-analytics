<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class EventExplorerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Explorer Test', 'domain' => 'explorer.test', 'tracking_id' => 'trk_explorer',
        ]);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $website->id);

        Analytics::event('purchase', ['plan' => 'pro'], 1500, ['visitor_hash' => 'f_one']);
        Analytics::event('purchase', ['plan' => 'free'], 0, ['visitor_hash' => 'f_two']);
        Analytics::event('signup', ['plan' => 'pro'], null, ['visitor_hash' => 'f_one']);
    }

    public function test_explorer_lists_events_with_filters(): void
    {
        $eventCell = fn (string $name): string => '<td class="py-2 px-4 font-mono font-medium text-slate-800 dark:text-slate-200">' . $name . '</td>';
        $visitorCell = fn (string $hash): string => '<td class="py-2 px-4 font-mono text-slate-500">' . $hash . '</td>';

        $this->get(route('lwa_explorer'))
            ->assertOk()
            ->assertSee('Event Explorer')
            ->assertSee('purchase')
            ->assertSee('signup')
            ->assertSee('f_one');

        $this->get(route('lwa_explorer', ['event' => 'purchase']))
            ->assertOk()
            ->assertSee($eventCell('purchase'), false)
            ->assertDontSee($eventCell('signup'), false);

        $this->get(route('lwa_explorer', ['property_key' => 'plan', 'property_value' => 'free']))
            ->assertOk()
            ->assertSee($visitorCell('f_two'), false)
            ->assertDontSee($visitorCell('f_one'), false);

        $this->get(route('lwa_explorer', ['user' => 'f_one']))
            ->assertOk()
            ->assertSee($visitorCell('f_one'), false)
            ->assertDontSee($visitorCell('f_two'), false);
    }

    public function test_explorer_rejects_invalid_range(): void
    {
        $this->get(route('lwa_explorer', ['range' => 'not-a-range']))->assertStatus(302);
    }
}
