<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\ComparisonService;
use Nafiur\WebAnalytics\Tests\TestCase;

class ComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_previous_window_has_equal_length_and_shifts_back(): void
    {
        config()->set('app.timezone', 'UTC');
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-26 12:00:00', 'UTC'));

        [$from, $to] = AnalyticsWindow::previous(['range' => '7d']);
        $this->assertSame('2026-11-12T12:00:00+00:00', $from);
        $this->assertSame('2026-11-19T12:00:00+00:00', $to);

        [$customFrom, $customTo] = AnalyticsWindow::previous([
            'range'   => 'custom',
            'start_date' => '2026-11-20',
            'end_date'   => '2026-11-24',
        ]);
        $this->assertSame('2026-11-15T00:00:00+00:00', $customFrom);
        $this->assertSame('2026-11-20T00:00:00+00:00', $customTo);

        $this->assertSame([null, null], AnalyticsWindow::previous(['range' => 'all']));
    }

    public function test_comparison_service_uses_relative_and_percentage_point_rules(): void
    {
        $deltas = app(ComparisonService::class)->deltas(
            ['total_visitors' => 30, 'bounce_rate' => 12.5, 'sessions' => 5],
            ['total_visitors' => 20, 'bounce_rate' => 10.0, 'sessions' => 0],
        );

        $this->assertSame(50.0, $deltas['total_visitors']['change']);
        $this->assertSame('percent', $deltas['total_visitors']['unit']);
        $this->assertSame('up', $deltas['total_visitors']['direction']);
        $this->assertSame(2.5, $deltas['bounce_rate']['change']);
        $this->assertSame('points', $deltas['bounce_rate']['unit']);
        $this->assertTrue($deltas['sessions']['is_new']);
        $this->assertNull($deltas['sessions']['change']);
        $this->assertFalse($deltas['avg_engagement_time']['available']);
    }

    public function test_dashboard_renders_and_exposes_previous_period_deltas(): void
    {
        foreach ([['s1', now()->subHour()], ['s2', now()->subMinutes(30)]] as [$session, $at]) {
            LwaLogs::create([
                'ip_address'       => '127.0.0.1',
                'session_id'       => $session,
                'method'           => 'GET',
                'path'             => '/products',
                'status_code'      => 200,
                'response_time_ms' => 20,
                'bytes_sent'       => 1024,
                'created_at'       => $at,
                'updated_at'       => $at,
            ]);
        }
        $prior = now()->subDays(2);
        LwaLogs::create([
            'ip_address'       => '127.0.0.1',
            'session_id'       => 's_prior',
            'method'           => 'GET',
            'path'             => '/products',
            'status_code'      => 200,
            'response_time_ms' => 20,
            'bytes_sent'       => 1024,
            'created_at'       => $prior,
            'updated_at'       => $prior,
        ]);

        $this->get(route('lwa_dashboard', ['range' => '24h', 'compare' => 1]))
            ->assertOk()
            ->assertSee('Compare prior')
            ->assertSee('vs prior');

        $this->getJson(route('lwa_data', ['range' => '24h', 'compare' => 1]))
            ->assertOk()
            ->assertJsonPath('comparison.total_visitors.available', true)
            ->assertJsonPath('comparison.unique_visitors.direction', 'up');

        $this->getJson(route('lwa_data', ['range' => '24h']))
            ->assertOk()
            ->assertJsonPath('comparison', null);
    }
}
