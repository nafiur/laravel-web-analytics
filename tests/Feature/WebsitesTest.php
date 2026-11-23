<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class WebsitesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Website::create(['name' => 'First', 'domain' => 'first.test', 'tracking_id' => 'trk_first']);
        Website::create(['name' => 'Second', 'domain' => 'second.test', 'tracking_id' => 'trk_second']);

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.website_id', Website::first()->id);
    }

    public function test_settings_page_lists_websites_and_selector(): void
    {
        $content = $this->get(route('lwa_settings'))->getContent();

        $this->assertStringContainsString('settings-websites', $content);
        $this->assertStringContainsString('first.test', $content);
        $this->assertStringContainsString('second.test', $content);
        $this->assertStringContainsString('Switch', $content);
    }

    public function test_website_can_be_switched_for_the_session(): void
    {
        $second = Website::where('domain', 'second.test')->first();

        $this->post(route('lwa_websites_switch'), ['website' => $second->id])
            ->assertRedirect();

        $this->assertSame($second->id, session('lwa_website_id'));

        $content = $this->get(route('lwa_overview'))->getContent();
        $this->assertStringContainsString('second.test', $content);
    }

    public function test_website_can_be_registered(): void
    {
        $this->post(route('lwa_websites_store'), [
            'name'   => 'Third',
            'domain' => 'https://third.test/some/path',
        ])->assertRedirect();

        $this->assertSame(3, Website::count());
        $this->assertNotNull(Website::where('domain', 'third.test')->first());
    }

    public function test_website_removal_requires_confirmation(): void
    {
        $first = Website::where('domain', 'first.test')->first();

        $this->delete(route('lwa_websites_destroy', $first->id), ['confirm' => 'NO'])
            ->assertSessionHasErrors('confirm');
        $this->assertSame(2, Website::count());

        $this->delete(route('lwa_websites_destroy', $first->id), ['confirm' => 'DELETE'])
            ->assertRedirect();
        $this->assertSame(1, Website::count());
    }

    public function test_last_website_cannot_be_removed(): void
    {
        Website::where('domain', 'second.test')->delete();
        $last = Website::first();

        $this->delete(route('lwa_websites_destroy', $last->id), ['confirm' => 'DELETE'])
            ->assertSessionHasErrors('website');
        $this->assertSame(1, Website::count());
    }
}
