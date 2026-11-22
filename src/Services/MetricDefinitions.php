<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;

/**
 * Authoritative v3 metric definitions. Pure functions — no database access —
 * so boundary cases can be unit-tested before calculators are implemented.
 */
class MetricDefinitions
{
    public const DEFAULT_SESSION_TIMEOUT_MINUTES = 30;

    public static function sessionTimeoutMinutes(): int
    {
        return max(1, (int) config('laravel-web-analytics.v3.session_timeout_minutes', self::DEFAULT_SESSION_TIMEOUT_MINUTES));
    }

    /**
     * A bounce is a session with exactly one pageview and no meaningful
     * active time (configurable threshold, default 0 seconds).
     */
    public static function isBounce(int $pageviews, int $durationSeconds, ?int $threshold = null): bool
    {
        $threshold = $threshold ?? (int) config('laravel-web-analytics.v3.bounce_max_duration_seconds', 0);

        return $pageviews === 1 && $durationSeconds <= $threshold;
    }

    /**
     * True when a new hit belongs to the same session (inactivity timeout).
     */
    public static function isSameSession(mixed $lastAt, mixed $currentAt, ?int $timeoutMinutes = null): bool
    {
        if ($lastAt === null || $currentAt === null) {
            return false;
        }

        $gap = abs(Carbon::parse($currentAt)->diffInSeconds(Carbon::parse($lastAt)));

        return $gap <= self::sessionTimeoutMinutes() * 60;
    }

    /**
     * Sum of active intervals between consecutive pageviews. Each gap is
     * capped at the inactivity timeout; a single pageview yields zero.
     *
     * @param array<int, mixed> $timestamps
     */
    public static function engagementSeconds(array $timestamps, ?int $timeoutMinutes = null): int
    {
        $times = array_map(fn ($time) => Carbon::parse($time), array_values($timestamps));
        usort($times, fn ($a, $b) => $a->timestamp <=> $b->timestamp);

        $cap = ($timeoutMinutes ?? self::sessionTimeoutMinutes()) * 60;
        $total = 0;

        for ($i = 1; $i < count($times); $i++) {
            $gap = abs($times[$i]->diffInSeconds($times[$i - 1]));
            $total += min(max($gap, 0), $cap);
        }

        return $total;
    }

    /**
     * Conversion rate with an explicit denominator (sessions).
     */
    public static function conversionRate(int $conversions, int $sessions): float
    {
        return $sessions > 0 ? round(($conversions / $sessions) * 100, 2) : 0.0;
    }
}
