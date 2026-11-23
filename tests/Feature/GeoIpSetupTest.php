<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Nafiur\WebAnalytics\Tests\TestCase;

class GeoIpSetupTest extends TestCase
{
    public function test_geoip_command_reports_reader_and_database_status(): void
    {
        $this->artisan('lwa:geoip')
            ->expectsOutputToContain('Offline GeoIP setup')
            ->expectsOutputToContain('Reader library')
            ->expectsOutputToContain('Database file')
            ->assertExitCode(1); // optional reader/db are not installed in the test suite
    }

    public function test_geoip_alias_is_registered(): void
    {
        $this->artisan('analytics:geoip')->assertExitCode(1);
    }
}
