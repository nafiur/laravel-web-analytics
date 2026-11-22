<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;

/**
 * Builds hourly/daily rollups from raw v3 analytics tables. Dashboard queries
 * should read these rows instead of scanning pageviews.
 */
class AggregationService
{
    /** @return mixed the created/updated stat row */
    public function aggregate(int $websiteId, Carbon $bucketStart, string $granularity = 'hourly'): mixed
    {
        $bucketStart = $granularity === 'daily'
            ? $bucketStart->copy()->startOfDay()
            : $bucketStart->copy()->startOfHour();
        $bucketEnd = $granularity === 'daily'
            ? $bucketStart->copy()->addDay()
            : $bucketStart->copy()->addHour();

        $pageviews = AnalyticsModels\Pageview::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('created_at', '>=', $bucketStart)
            ->where('created_at', '<', $bucketEnd)
            ->selectRaw(
                'COUNT(*) as pageviews, '
                . 'COUNT(DISTINCT visitor_id) as visitors, '
                . 'COUNT(DISTINCT session_id) as sessions, '
                . 'COALESCE(SUM(bytes_sent), 0) as bytes, '
                . 'COALESCE(AVG(response_time_ms), 0) as avg_response_ms, '
                . 'SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as errors_4xx, '
                . 'SUM(CASE WHEN status_code >= 500 AND status_code < 600 THEN 1 ELSE 0 END) as errors_5xx'
            )
            ->first();

        $sessionQuery = AnalyticsModels\AnalyticsSession::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('started_at', '>=', $bucketStart)
            ->where('started_at', '<', $bucketEnd);

        $bounces = (clone $sessionQuery)->where('is_bounce', true)->count();
        $engagement = (clone $sessionQuery)->sum('duration_seconds');

        $values = [
            'visitors'           => (int) ($pageviews->visitors ?? 0),
            'sessions'           => (int) ($pageviews->sessions ?? 0),
            'pageviews'          => (int) ($pageviews->pageviews ?? 0),
            'bounces'            => (int) $bounces,
            'engagement_seconds' => (int) $engagement,
            'bytes'              => (int) ($pageviews->bytes ?? 0),
            'errors_4xx'         => (int) ($pageviews->errors_4xx ?? 0),
            'errors_5xx'         => (int) ($pageviews->errors_5xx ?? 0),
            'avg_response_ms'    => round((float) ($pageviews->avg_response_ms ?? 0), 2),
        ];

        $modelClass = $granularity === 'daily'
            ? AnalyticsModels\DailyStat::class
            : AnalyticsModels\HourlyStat::class;

        return $modelClass::withoutGlobalScope(WebsiteScope::class)->updateOrCreate(
            ['website_id' => $websiteId, 'bucket_at' => $bucketStart],
            $values
        );
    }

    /**
     * Aggregate every bucket in an inclusive date range.
     *
     * @return int number of buckets processed
     */
    public function aggregateRange(int $websiteId, Carbon $from, Carbon $to, string $granularity = 'hourly', int $maxBuckets = 1000): int
    {
        $cursor = $granularity === 'daily' ? $from->copy()->startOfDay() : $from->copy()->startOfHour();
        $end = $granularity === 'daily' ? $to->copy()->startOfDay() : $to->copy()->startOfHour();
        $count = 0;

        while ($cursor->lte($end) && $count < $maxBuckets) {
            $this->aggregate($websiteId, $cursor, $granularity);
            $granularity === 'daily' ? $cursor->addDay() : $cursor->addHour();
            $count++;
        }

        return $count;
    }
}
