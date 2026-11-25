<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;

/**
 * Shared time-window resolution for the Explorer and Insights. Every window
 * is expressed in the host application's configured timezone and returned as
 * ISO-8601 strings so the EventStore behaves identically on SQLite, MySQL and
 * PostgreSQL.
 */
final class AnalyticsWindow
{
    public const RANGES = ['1h', '24h', '7d', '30d', '90d', 'all', 'custom'];

    /**
     * @param array<string, mixed> $filters
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    public static function resolve(array $filters): array
    {
        $range = isset($filters['range']) && in_array($filters['range'], self::RANGES, true)
            ? (string) $filters['range']
            : '7d';

        if ($range === 'all') {
            return [null, null, $range];
        }

        if ($range === 'custom') {
            $tz = (string) config('app.timezone', 'UTC');
            $start = isset($filters['start_date']) && $filters['start_date'] !== ''
                ? CarbonImmutable::parse((string) $filters['start_date'], $tz)->startOfDay()
                : null;
            $end = isset($filters['end_date']) && $filters['end_date'] !== ''
                ? CarbonImmutable::parse((string) $filters['end_date'], $tz)->endOfDay()
                : null;

            return [
                $start?->toIso8601String(),
                $end?->toIso8601String(),
                $range,
            ];
        }

        $now = CarbonImmutable::now()->setTimezone((string) config('app.timezone', 'UTC'));
        $from = match ($range) {
            '1h'  => $now->subHour(),
            '24h' => $now->subDay(),
            '30d' => $now->subDays(30),
            '90d' => $now->subDays(90),
            default => $now->subDays(7),
        };

        return [$from->toIso8601String(), $now->toIso8601String(), $range];
    }
}
