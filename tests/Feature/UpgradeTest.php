<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticsSession;
use Nafiur\WebAnalytics\Models\Analytics\HourlyStat;
use Nafiur\WebAnalytics\Models\Analytics\Pageview;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\UpgradeService;
use Nafiur\WebAnalytics\Tests\TestCase;

class UpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_counts_rows_without_writing(): void
    {
        $this->seedLegacyLogs();

        $result = app(UpgradeService::class)->run(['dry_run' => true]);

        $this->assertSame(3, $result['read']);
        $this->assertSame(0, Pageview::count());
        $this->assertSame(0, AnalyticsSession::count());
    }

    public function test_upgrade_backfills_and_can_be_resumed(): void
    {
        $this->seedLegacyLogs();

        $first = app(UpgradeService::class)->run(['website_domain' => 'example.test']);
        $this->assertSame(3, $first['pageviews']);
        $this->assertSame(2, $first['sessions']);
        $this->assertSame(2, $first['visitors']);
        $this->assertSame(3, Pageview::count());
        $this->assertGreaterThanOrEqual(1, HourlyStat::count());

        $second = app(UpgradeService::class)->run(['website_domain' => 'example.test']);
        $this->assertSame(0, $second['read']);
        $this->assertSame(3, Pageview::count());
    }

    public function test_prune_legacy_removes_imported_rows(): void
    {
        $this->seedLegacyLogs();

        $result = app(UpgradeService::class)->run([
            'website_domain' => 'example.test',
            'prune_legacy'   => true,
        ]);

        $this->assertSame(3, $result['pruned']);
        $this->assertSame(0, LwaLogs::count());
        $this->assertSame(3, Pageview::count());
    }

    public function test_upgrade_fails_cleanly_when_schema_is_missing(): void
    {
        \Illuminate\Support\Facades\Schema::drop('lwa_websites');

        $this->artisan('lwa:upgrade')
            ->expectsOutput('Advanced analytics tables are missing: lwa_websites')
            ->assertExitCode(1);
    }

    public function test_upgrade_command_alias_runs(): void
    {
        $this->seedLegacyLogs();

        $this->artisan('analytics:upgrade', ['--dry-run' => true])->assertExitCode(0);
    }

    /** @param array<string, mixed> $overrides */
    private function seedLegacyLogs(array $overrides = []): void
    {
        LwaLogs::create($this->legacyLog(array_merge([
            'session_id' => 's_session_1',
            'ip_address' => '8.8.8.8',
            'path'       => '/a',
        ], $overrides)));
        LwaLogs::create($this->legacyLog(array_merge([
            'session_id' => 's_session_1',
            'ip_address' => '8.8.8.8',
            'path'       => '/b',
        ], $overrides)));
        LwaLogs::create($this->legacyLog(array_merge([
            'session_id' => null,
            'ip_address' => '1.1.1.1',
            'path'       => '/c',
            'user_agent' => 'TestAgent/1.0',
        ], $overrides)));
    }

    /** @param array<string, mixed> $overrides */
    private function legacyLog(array $overrides = []): array
    {
        return array_merge([
            'ip_address'       => '127.0.0.1',
            'session_id'       => 's_default',
            'method'           => 'GET',
            'path'             => '/',
            'status_code'      => 200,
            'response_time_ms' => 12.5,
            'bytes_sent'       => 100,
            'user_agent'       => 'Mozilla/5.0',
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides);
    }
}
