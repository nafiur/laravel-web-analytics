<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Services\SchemaGuard;
use Nafiur\WebAnalytics\Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_produces_a_full_report(): void
    {
        $exit = Artisan::call('lwa:doctor', ['--json' => true]);
        $report = json_decode(Artisan::output(), true);

        $this->assertSame(0, $exit);
        $this->assertArrayHasKey('checks', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertGreaterThanOrEqual(10, count($report['checks']));
        $this->assertContains('Advanced analytics tables', array_column($report['checks'], 'check'));
    }

    public function test_doctor_fails_when_advanced_tables_are_missing(): void
    {
        foreach (SchemaGuard::REQUIRED_TABLES as $table) {
            Schema::drop($table);
        }

        $exit = Artisan::call('lwa:doctor', ['--json' => true]);
        $report = json_decode(Artisan::output(), true);

        $this->assertSame(1, $exit);
        $this->assertSame('fail', $report['summary']['status']);
        $this->assertGreaterThanOrEqual(1, $report['summary']['failures']);
    }

    public function test_doctor_alias_is_registered(): void
    {
        $this->artisan('analytics:doctor')->assertExitCode(0);
    }
}
