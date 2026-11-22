<?php

namespace Nafiur\WebAnalytics\Tests\Unit;

use Nafiur\WebAnalytics\Services\MetricDefinitions;
use Nafiur\WebAnalytics\Tests\TestCase;

class MetricDefinitionsTest extends TestCase
{
    public function test_bounce_requires_single_pageview_and_no_active_time(): void
    {
        $this->assertTrue(MetricDefinitions::isBounce(1, 0));
        $this->assertFalse(MetricDefinitions::isBounce(2, 0));
        $this->assertFalse(MetricDefinitions::isBounce(1, 30));
        $this->assertTrue(MetricDefinitions::isBounce(1, 30, 60));
    }

    public function test_session_timeout_boundary(): void
    {
        $now = now();

        $this->assertTrue(MetricDefinitions::isSameSession($now, $now->copy()->addMinutes(30)));
        $this->assertFalse(MetricDefinitions::isSameSession($now, $now->copy()->addMinutes(31)));
        $this->assertFalse(MetricDefinitions::isSameSession(null, $now));
    }

    public function test_engagement_caps_idle_gaps(): void
    {
        $start = now()->startOfSecond();

        $this->assertSame(25, MetricDefinitions::engagementSeconds([
            $start->copy()->addSeconds(25)->toIso8601String(),
            $start->toIso8601String(),
            $start->copy()->addSeconds(10)->toIso8601String(),
        ]));

        $this->assertSame(1800, MetricDefinitions::engagementSeconds([
            $start->toIso8601String(),
            $start->copy()->addHours(2)->toIso8601String(),
        ]));

        $this->assertSame(0, MetricDefinitions::engagementSeconds([$start->toIso8601String()]));
    }

    public function test_conversion_rate_uses_sessions_as_denominator(): void
    {
        $this->assertSame(25.0, MetricDefinitions::conversionRate(5, 20));
        $this->assertSame(0.0, MetricDefinitions::conversionRate(5, 0));
    }
}
