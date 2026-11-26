<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Services\PreflightService;
use Nafiur\WebAnalytics\Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_page_reports_the_preflight_checks(): void
    {
        config()->set('laravel-web-analytics.health.enabled', true);

        $this->get(route('lwa_health'))
            ->assertOk()
            ->assertSee('Installation Health')
            ->assertSee('PHP version')
            ->assertSee('Database connection')
            ->assertSee('Advanced analytics tables')
            ->assertSee('Next step')
            ->assertSee('Checks');

        $report = app(PreflightService::class)->run();
        $this->assertGreaterThanOrEqual(12, count($report['checks']));
        $this->assertArrayHasKey('status', $report['summary']);
        $this->assertArrayHasKey('passed', $report['summary']);
        $this->assertArrayHasKey('warnings', $report['summary']);
        $this->assertArrayHasKey('failures', $report['summary']);
        $this->assertArrayHasKey('next', $report['summary']);
    }

    public function test_health_page_is_hidden_when_disabled(): void
    {
        config()->set('laravel-web-analytics.health.enabled', false);
        $this->get(route('lwa_health'))->assertNotFound();
    }
}
