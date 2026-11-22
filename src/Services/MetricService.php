<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;

/**
 * Reporting service for the advanced analytics schema. Reads rollup tables
 * only — raw pageviews are never scanned for dashboard KPIs.
 */
class MetricService
{
    /** @return array{0: Carbon, 1: Carbon} */
    public function resolveRange(string $range, ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = now();

        return match ($range) {
            '1h'    => [$now->copy()->subHour(), $now->copy()],
            '6h'    => [$now->copy()->subHours(6), $now->copy()],
            '24h'   => [$now->copy()->subHours(24), $now->copy()],
            'today' => [$now->copy()->startOfDay(), $now->copy()],
            '7d'    => [$now->copy()->subDays(7)->startOfDay(), $now->copy()],
            '30d'   => [$now->copy()->subDays(30)->startOfDay(), $now->copy()],
            'custom'=> [
                $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->subDays(30),
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy(),
            ],
            default => [$now->copy()->subDays(365)->startOfDay(), $now->copy()],
        };
    }

    /** @return array<string, mixed> */
    public function summary(int $websiteId, string $range = '24h', ?string $customStart = null, ?string $customEnd = null): array
    {
        [$from, $to] = $this->resolveRange($range, $customStart, $customEnd);
        $rows = $this->rows($websiteId, $from, $to);

        $pageviews = $rows->sum('pageviews');
        $sessions = $rows->sum('sessions');
        $visitors = $rows->sum('visitors');
        $bytes = $rows->sum('bytes');
        $bounces = $rows->sum('bounces');
        $engagement = $rows->sum('engagement_seconds');
        $errors = $rows->sum('errors_4xx') + $rows->sum('errors_5xx');
        $weightedResponse = $pageviews > 0
            ? $rows->sum(fn ($row) => (float) $row->avg_response_ms * (int) $row->pageviews) / $pageviews
            : 0;

        return [
            'total_requests'      => (int) $pageviews,
            'total_requests_fmt'  => $this->number($pageviews),
            'pageviews'           => (int) $pageviews,
            'pageviews_fmt'       => $this->number($pageviews),
            'unique_visitors'     => (int) $visitors,
            'unique_visitors_fmt' => $this->number($visitors),
            'total_visitors'      => (int) $visitors,
            'total_visitors_fmt'  => $this->number($visitors),
            'sessions'            => (int) $sessions,
            'sessions_fmt'        => $this->number($sessions),
            'bounce_rate'         => $sessions > 0 ? round(($bounces / $sessions) * 100, 2) : 0.0,
            'avg_engagement_time' => $sessions > 0 ? (int) round($engagement / $sessions) : 0,
            'avg_engagement_fmt'  => $sessions > 0 ? $this->duration((int) round($engagement / $sessions)) : '0s',
            'avg_response_time'   => round($weightedResponse, 2),
            'total_bytes'         => (int) $bytes,
            'total_bytes_fmt'     => $this->bytes($bytes),
            'error_rate'          => $pageviews > 0 ? round(($errors / $pageviews) * 100, 2) : 0.0,
            'error_count'         => (int) $errors,
            'time_range'          => $range,
            'storage_type'        => 'Aggregated analytics',
        ];
    }

    /** @return array<string, mixed> */
    public function timeseries(int $websiteId, string $range = '24h', ?string $customStart = null, ?string $customEnd = null): array
    {
        [$from, $to] = $this->resolveRange($range, $customStart, $customEnd);
        $granularity = $to->diffInHours($from) <= 48 ? 'hourly' : 'daily';
        $rows = $this->rows($websiteId, $from, $to, $granularity);

        return [
            'granularity'   => $granularity,
            'labels'        => $rows->map(fn ($row) => Carbon::parse($row->bucket_at)
                ->format($granularity === 'hourly' ? 'H:i' : 'd M'))->all(),
            'timestamps'    => $rows->map(fn ($row) => Carbon::parse($row->bucket_at)->toIso8601String())->all(),
            'requests'      => $rows->pluck('pageviews')->map(fn ($v) => (int) $v)->all(),
            'visitors'      => $rows->pluck('visitors')->map(fn ($v) => (int) $v)->all(),
            'sessions'      => $rows->pluck('sessions')->map(fn ($v) => (int) $v)->all(),
            'bandwidth_mb'  => $rows->pluck('bytes')->map(fn ($v) => round((int) $v / 1048576, 2))->all(),
            'errors'        => $rows->map(fn ($row) => (int) $row->errors_4xx + (int) $row->errors_5xx)->all(),
        ];
    }

    private function rows(int $websiteId, Carbon $from, Carbon $to, ?string $granularity = null): \Illuminate\Support\Collection
    {
        $granularity ??= $to->diffInHours($from) <= 48 ? 'hourly' : 'daily';
        $modelClass = $granularity === 'daily'
            ? AnalyticsModels\DailyStat::class
            : AnalyticsModels\HourlyStat::class;

        return $modelClass::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('bucket_at', '>=', $from)
            ->where('bucket_at', '<=', $to)
            ->orderBy('bucket_at')
            ->get();
    }

    private function number(int|float $value): string
    {
        if ($value >= 1000000) return round($value / 1000000, 2) . 'M';
        if ($value >= 1000) return round($value / 1000, 1) . 'K';

        return number_format($value);
    }

    private function bytes(int|float $value): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = $value > 0 ? (int) floor(log($value, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        return round($value / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    private function duration(int $seconds): string
    {
        return $seconds >= 60
            ? floor($seconds / 60) . 'm ' . ($seconds % 60) . 's'
            : $seconds . 's';
    }
}
