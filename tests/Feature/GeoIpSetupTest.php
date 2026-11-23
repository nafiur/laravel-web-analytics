<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Nafiur\WebAnalytics\Tests\TestCase;

class GeoIpSetupTest extends TestCase
{
    public function test_map_hint_does_not_ask_to_install_when_geoip_is_ready(): void
    {
        $ready = view('web-analytics::partials.visitors-map', [
            'mapId' => 'testReadyMap',
            'geoipReady' => true,
        ])->render();

        $this->assertStringNotContainsString('Install', $ready);
        $this->assertStringContainsString('No located visitors in this period yet.', $ready);

        $pending = view('web-analytics::partials.visitors-map', [
            'mapId' => 'testPendingMap',
            'geoipReady' => false,
        ])->render();

        $this->assertStringContainsString('city-level precision', $pending);
    }

    public function test_map_receives_top_country_codes_for_highlighting(): void
    {
        $html = view('web-analytics::partials.visitors-map', [
            'mapId' => 'highlightMap',
            'analytics' => [
                'breakdowns' => [
                    'top_countries' => [
                        ['name' => 'Bangladesh'],
                        ['name' => 'United States'],
                    ],
                ],
            ],
        ])->render();

        $this->assertStringContainsString('data-highlight-countries=', $html);
        $this->assertStringContainsString('BD', $html);
        $this->assertStringContainsString('US', $html);
    }

    public function test_geoip_command_reports_reader_and_database_status(): void
    {
        $this->artisan('lwa:geoip')
            ->expectsOutputToContain('Offline GeoIP setup')
            ->expectsOutputToContain('Reader library')
            ->expectsOutputToContain('Database file')
            ->assertExitCode(1); // optional reader/db are not installed in the test suite
    }

    public function test_auto_mode_is_disabled_by_config(): void
    {
        config()->set('laravel-web-analytics.geoip.auto_download', false);

        $this->artisan('lwa:geoip', ['--auto' => true])
            ->expectsOutputToContain('Automatic GeoIP download is disabled')
            ->assertExitCode(0);
    }

    public function test_auto_mode_skips_when_no_license_is_configured(): void
    {
        config()->set('laravel-web-analytics.geoip.auto_download', true);
        config()->set('laravel-web-analytics.geoip.license_key', null);
        config()->set('laravel-web-analytics.geoip.maxmind_db_path', '/tmp/nonexistent-lwa-geoip.mmdb');

        $this->artisan('lwa:geoip', ['--auto' => true])
            ->expectsOutputToContain('no MAXMIND_LICENSE_KEY')
            ->assertExitCode(0);
    }

    public function test_geoip_alias_is_registered(): void
    {
        $this->artisan('analytics:geoip')->assertExitCode(1);
    }
}
