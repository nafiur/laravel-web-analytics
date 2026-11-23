<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\SchemaGuard;
use Nafiur\WebAnalytics\Tests\TestCase;

class ReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_setup_page_instead_of_crashing(): void
    {
        $this->dropAllTables();

        $this->get(route('lwa_dashboard'))
            ->assertOk()
            ->assertSee('Analytics database is not ready');
    }

    public function test_data_endpoint_reports_503_without_stack_trace(): void
    {
        $this->dropAllTables();

        $response = $this->getJson(route('lwa_data'));

        $response->assertStatus(503)->assertJsonPath('success', false);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    public function test_stream_emits_error_event_when_not_ready(): void
    {
        $this->dropAllTables();

        $this->get(route('lwa_stream'))
            ->assertOk()
            ->assertSee('event: error', false);
    }

    public function test_api_returns_503_when_advanced_schema_missing(): void
    {
        foreach (SchemaGuard::REQUIRED_TABLES as $table) {
            Schema::drop($table);
        }

        $this->getJson(route('lwa_api_summary'))->assertStatus(503);
    }

    public function test_prune_command_fails_cleanly_when_legacy_table_missing(): void
    {
        Schema::drop(DatabaseReadiness::legacyTable());

        $this->artisan('lwa:prune')->assertExitCode(1);
    }

    private function dropAllTables(): void
    {
        Schema::drop(DatabaseReadiness::legacyTable());
        foreach (SchemaGuard::REQUIRED_TABLES as $table) {
            Schema::drop($table);
        }
    }
}
