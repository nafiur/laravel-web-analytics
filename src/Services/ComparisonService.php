<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Computes honest comparison deltas from two dashboard summaries. Count
 * metrics use relative percent change; rate metrics use percentage points so
 * a bounce-rate move is never presented as a relative conversion lift.
 */
class ComparisonService
{
    private const COUNT_KEYS = [
        'total_visitors' => 'Total Visitors',
        'unique_visitors' => 'Unique Visitors',
        'pageviews' => 'Pageviews',
        'sessions' => 'Sessions',
        'live_visitors' => 'Live Now',
    ];
    private const RATE_KEYS = [
        'bounce_rate' => 'Bounce Rate',
        'error_rate' => 'Error Rate',
    ];
    private const DURATION_KEYS = [
        'avg_engagement_time' => 'Avg Engagement',
        'avg_response_time' => 'Avg Response Time',
    ];

    /** @param array<string, mixed> $current @param array<string, mixed> $previous @return array<string, array<string, mixed>> */
    public function deltas(array $current, array $previous): array
    {
        $rows = [];
        foreach (self::COUNT_KEYS as $key => $label) {
            $rows[$key] = $this->row($key, $label, $current[$key] ?? null, $previous[$key] ?? null, 'percent');
        }
        foreach (self::RATE_KEYS as $key => $label) {
            $rows[$key] = $this->row($key, $label, $current[$key] ?? null, $previous[$key] ?? null, 'points');
        }
        foreach (self::DURATION_KEYS as $key => $label) {
            $rows[$key] = $this->row($key, $label, $current[$key] ?? null, $previous[$key] ?? null, 'percent');
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $key, string $label, mixed $current, mixed $previous, string $unit): array
    {
        $available = is_numeric($current) && is_numeric($previous);
        $currentValue = $available ? (float) $current : null;
        $previousValue = $available ? (float) $previous : null;
        $change = null;
        $direction = 'flat';

        if ($available) {
            if ($unit === 'points') {
                $change = round($currentValue - $previousValue, 2);
            } elseif ($previousValue > 0) {
                $change = round((($currentValue - $previousValue) / $previousValue) * 100, 2);
            } elseif ($currentValue > 0) {
                $change = null; // "new" rather than dividing by zero
            } else {
                $change = 0.0;
            }

            if ($change !== null && abs($change) > 0.005) {
                $direction = $change > 0 ? 'up' : 'down';
            }
        }

        return [
            'key'          => $key,
            'label'        => $label,
            'current'      => $currentValue,
            'previous'     => $previousValue,
            'change'       => $change,
            'unit'         => $unit,
            'direction'    => $direction,
            'available'    => $available,
            'is_new'       => $available && $change === null && $currentValue > 0,
            'definition'   => $unit === 'points'
                ? 'Percentage-point difference from the previous equal-length window.'
                : 'Relative percent change from the previous equal-length window.',
        ];
    }
}
