<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Nafiur\WebAnalytics\Tests\TestCase;

class VendorAssetTest extends TestCase
{
    public function test_rrweb_assets_are_served_when_replay_is_enabled(): void
    {
        config()->set('laravel-web-analytics.replay.enabled', true);

        $this->get(route('lwa_vendor_rrweb_js'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('g["rrweb"]', false);

        $this->get(route('lwa_vendor_rrweb_css'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=utf-8')
            ->assertSee('.replayer-wrapper', false);

        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->get(route('lwa_vendor_rrweb_js'))->assertNotFound();
        $this->get(route('lwa_vendor_rrweb_css'))->assertNotFound();
    }

    public function test_web_vitals_asset_is_served_when_sdk_vitals_are_enabled(): void
    {
        config()->set('laravel-web-analytics.sdk.enabled', true);
        config()->set('laravel-web-analytics.sdk.web_vitals', true);

        $this->get(route('lwa_vendor_web_vitals_js'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('onLCP', false)
            ->assertSee('onINP', false);

        config()->set('laravel-web-analytics.sdk.web_vitals', false);
        $this->get(route('lwa_vendor_web_vitals_js'))->assertNotFound();
    }
}
