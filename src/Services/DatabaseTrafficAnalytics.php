<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseTrafficAnalytics
{
    /**
     * Compute full dashboard analytics data from database table.
     */
    public function getDashboardData(string $timeRange = '24h', ?string $customStart = null, ?string $customEnd = null, ?string $tenantId = null): array
    {
        if ($tenantId === null && TenantResolver::isEnabled()) {
            $tenantId = TenantResolver::resolve();
        }

        $cutoff = $this->resolveCutoffTime($timeRange, $customStart, $customEnd);
        $start = $cutoff['start'];
        $end = $cutoff['end'];

        $baseQuery = LwaLogs::query();
        if ($tenantId !== null && $tenantId !== '') {
            $baseQuery->where('tenant_id', $tenantId);
        }
        if ($start) {
            $baseQuery->where('created_at', '>=', $start);
        }
        if ($end) {
            $baseQuery->where('created_at', '<=', $end);
        }

        // Summary Aggregations
        $summaryData = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_requests,
                COALESCE(SUM(bytes_sent), 0) as total_bytes,
                COUNT(DISTINCT COALESCE(session_id, ip_address)) as unique_visitors,
                COALESCE(AVG(response_time_ms), 0) as avg_response_time,
                SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as status_2xx,
                SUM(CASE WHEN status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) as status_3xx,
                SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as status_4xx,
                SUM(CASE WHEN status_code >= 500 AND status_code < 600 THEN 1 ELSE 0 END) as status_5xx
            ')
            ->first();

        $totalRequests = (int) ($summaryData->total_requests ?? 0);
        $totalBytes = (int) ($summaryData->total_bytes ?? 0);
        $uniqueVisitors = (int) ($summaryData->unique_visitors ?? 0);
        $avgResponseTime = round((float) ($summaryData->avg_response_time ?? 0), 2);

        $statusCounts = [
            '2xx'   => (int) ($summaryData->status_2xx ?? 0),
            '3xx'   => (int) ($summaryData->status_3xx ?? 0),
            '4xx'   => (int) ($summaryData->status_4xx ?? 0),
            '5xx'   => (int) ($summaryData->status_5xx ?? 0),
            'other' => max(0, $totalRequests - ((int)$summaryData->status_2xx + (int)$summaryData->status_3xx + (int)$summaryData->status_4xx + (int)$summaryData->status_5xx)),
        ];

        $errorCount = $statusCounts['4xx'] + $statusCounts['5xx'];
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0.0;
        $successRate = $totalRequests > 0 ? round(($statusCounts['2xx'] / $totalRequests) * 100, 2) : 0.0;

        $totalTableRecords = LwaLogs::forTenant($tenantId)->count();

        // Active Live Visitors in the last 5 minutes (Hybrid Session / User / IP)
        $fiveMinutesAgo = now()->subMinutes(5);
        $liveQuery = LwaLogs::forTenant($tenantId)->where('created_at', '>=', $fiveMinutesAgo);
        $liveActiveVisitors = (clone $liveQuery)
            ->distinct()
            ->count(DB::raw('COALESCE(session_id, ip_address)'));
        $liveActiveRequests5m = (clone $liveQuery)->count();

        // Time Series
        $timeSeries = $this->buildTimeSeries($timeRange, $start, $end, $tenantId);

        // Breakdowns
        $breakdowns = [
            'top_paths'           => $this->getTopBreakdown($baseQuery, 'path', $totalRequests, 10),
            'top_paths_bandwidth' => $this->getTopBandwidthBreakdown($baseQuery, 10),
            'top_ips'             => $this->getTopBreakdown($baseQuery, 'ip_address', $totalRequests, 10),
            'top_countries'       => $this->getTopBreakdown($baseQuery, 'country', $totalRequests, 10),
            'top_browsers'        => $this->getTopBreakdown($baseQuery, 'browser', $totalRequests, 8),
            'top_os'              => $this->getTopBreakdown($baseQuery, 'os', $totalRequests, 8),
            'top_devices'         => $this->getTopBreakdown($baseQuery, 'device', $totalRequests, 5),
            'top_status_codes'    => $this->getTopBreakdown($baseQuery, 'status_code', $totalRequests, 8),
            'top_referrers'       => $this->getTopBreakdown($baseQuery, 'referrer', $totalRequests, 8),
            'top_methods'         => $this->getTopBreakdown($baseQuery, 'method', $totalRequests, 6),
        ];

        // Recent Live Logs (latest 20 requests) - without any User model dependency
        $recentLogs = (clone $baseQuery)
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                $isAuth = !empty($log->user_id);
                $userName = $isAuth ? "User #{$log->user_id}" : 'Guest';
                return [
                    'id'               => $log->id,
                    'method'           => $log->method,
                    'path'             => $log->path,
                    'status_code'      => $log->status_code,
                    'response_time_ms' => $log->response_time_ms,
                    'bytes_sent_fmt'   => $this->formatBytes($log->bytes_sent),
                    'ip_address'       => $log->ip_address,
                    'browser'          => $log->browser,
                    'os'               => $log->os,
                    'device'           => $log->device,
                    'country'          => $log->country ?: 'Unknown',
                    'city'             => $log->city,
                    'user_id'          => $log->user_id,
                    'user_name'        => $userName,
                    'is_auth'          => $isAuth,
                    'time_ago'         => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
                    'time_clock'       => $log->created_at ? $log->created_at->format('H:i:s') : '',
                ];
            })
            ->toArray();

        // Top Error URLs (status_code >= 400)
        $topErrorPaths = (clone $baseQuery)
            ->where('status_code', '>=', 400)
            ->select('path', 'status_code', 'method', DB::raw('COUNT(*) as count'), DB::raw('MAX(created_at) as last_seen'))
            ->groupBy('path', 'status_code', 'method')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                return [
                    'path'           => $row->path,
                    'status_code'    => (int) $row->status_code,
                    'method'         => $row->method,
                    'count'          => (int) $row->count,
                    'count_fmt'      => number_format($row->count),
                    'last_seen'      => $row->last_seen ? Carbon::parse($row->last_seen)->diffForHumans() : '',
                    'last_seen_time' => $row->last_seen ? Carbon::parse($row->last_seen)->format('Y-m-d H:i:s') : '',
                ];
            })
            ->toArray();

        // Recent Error Requests (status_code >= 400)
        $recentErrorLogs = (clone $baseQuery)
            ->where('status_code', '>=', 400)
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(function ($log) {
                $isAuth = !empty($log->user_id);
                $userName = $isAuth ? "User #{$log->user_id}" : 'Guest';
                return [
                    'id'               => $log->id,
                    'method'           => $log->method,
                    'path'             => $log->path,
                    'status_code'      => $log->status_code,
                    'response_time_ms' => $log->response_time_ms,
                    'bytes_sent_fmt'   => $this->formatBytes($log->bytes_sent),
                    'ip_address'       => $log->ip_address,
                    'browser'          => $log->browser,
                    'os'               => $log->os,
                    'device'           => $log->device,
                    'country'          => $log->country ?: 'Unknown',
                    'referrer'         => $log->referrer,
                    'user_id'          => $log->user_id,
                    'user_name'        => $userName,
                    'is_auth'          => $isAuth,
                    'time_ago'         => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
                    'time_clock'       => $log->created_at ? $log->created_at->format('H:i:s') : '',
                ];
            })
            ->toArray();

        // Sparklines
        $sparklineRequests = $timeSeries['requests'] ?: [0];
        $sparklineBytes = $timeSeries['bandwidth_mb'] ?: [0];

        return [
            'summary' => [
                'total_requests'      => $totalRequests,
                'total_requests_fmt'  => $this->formatNumber($totalRequests),
                'total_bytes'         => $totalBytes,
                'total_bytes_fmt'     => $this->formatBytes($totalBytes),
                'unique_visitors'     => $uniqueVisitors,
                'unique_visitors_fmt' => $this->formatNumber($uniqueVisitors),
                'live_visitors'       => max(1, $liveActiveVisitors),
                'live_visitors_fmt'   => number_format(max(1, $liveActiveVisitors)),
                'live_requests_5m'    => $liveActiveRequests5m,
                'avg_response_time'   => $avgResponseTime,
                'error_rate'          => $errorRate,
                'error_count'         => $errorCount,
                'error_count_fmt'     => number_format($errorCount),
                'success_rate'        => $successRate,
                'status_counts'       => $statusCounts,
                'time_range'          => $timeRange,
                'log_file'            => 'Database Table (' . config('laravel-web-analytics.table_name', 'lwa_logs') . ')',
                'log_size'            => $this->formatNumber($totalTableRecords) . ' Rows',
                'storage_type'        => 'Database',
                'tenant_id'           => $tenantId,
                'is_multi_tenant'     => TenantResolver::isEnabled(),
            ],
            'time_series' => $timeSeries,
            'sparklines' => [
                'requests' => $sparklineRequests,
                'bytes'    => $sparklineBytes,
            ],
            'breakdowns'        => $breakdowns,
            'recent_logs'       => $recentLogs,
            'top_error_paths'   => $topErrorPaths,
            'recent_error_logs' => $recentErrorLogs,
        ];
    }

    /**
     * Build Time Series Buckets (Hourly or Daily) with multi-database SQL support.
     */
    protected function buildTimeSeries(string $timeRange, ?Carbon $start, ?Carbon $end, ?string $tenantId = null): array
    {
        $isHourly = in_array($timeRange, ['6h', '12h', '24h', 'today']);
        $query = LwaLogs::query();
        if ($tenantId !== null && $tenantId !== '') {
            $query->where('tenant_id', $tenantId);
        }
        if ($start) {
            $query->where('created_at', '>=', $start);
        }
        if ($end) {
            $query->where('created_at', '<=', $end);
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $dateExpr = $isHourly ? "strftime('%Y-%m-%d %H:00', created_at)" : "strftime('%Y-%m-%d', created_at)";
        } elseif ($driver === 'pgsql') {
            $dateExpr = $isHourly ? "to_char(created_at, 'YYYY-MM-DD HH24:00')" : "to_char(created_at, 'YYYY-MM-DD')";
        } else {
            // MySQL, MariaDB
            $dateFormat = $isHourly ? '%Y-%m-%d %H:00' : '%Y-%m-%d';
            $dateExpr = "DATE_FORMAT(created_at, '{$dateFormat}')";
        }

        $rows = $query->selectRaw("
            {$dateExpr} as bucket_time,
            COUNT(*) as req_count,
            COALESCE(SUM(bytes_sent), 0) as byte_sum,
            COALESCE(AVG(response_time_ms), 0) as avg_latency,
            SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as s2xx,
            SUM(CASE WHEN status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) as s3xx,
            SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as s4xx,
            SUM(CASE WHEN status_code >= 500 AND status_code < 600 THEN 1 ELSE 0 END) as s5xx
        ")
        ->groupBy('bucket_time')
        ->orderBy('bucket_time', 'ASC')
        ->get();

        $labels = [];
        $requests = [];
        $bandwidthMb = [];
        $latencies = [];
        $status2xx = [];
        $status3xx = [];
        $status4xx = [];
        $status5xx = [];

        foreach ($rows as $row) {
            try {
                $dt = Carbon::parse($row->bucket_time);
                $labels[] = $isHourly ? $dt->format('H:i') : $dt->format('d M');
            } catch (\Throwable $e) {
                $labels[] = (string) $row->bucket_time;
            }

            $requests[] = (int) $row->req_count;
            $bandwidthMb[] = round(((int) $row->byte_sum) / (1024 * 1024), 2);
            $latencies[] = round((float) $row->avg_latency, 2);
            $status2xx[] = (int) $row->s2xx;
            $status3xx[] = (int) $row->s3xx;
            $status4xx[] = (int) $row->s4xx;
            $status5xx[] = (int) $row->s5xx;
        }

        if (empty($labels)) {
            $labels = ['No Data'];
            $requests = [0];
            $bandwidthMb = [0];
            $latencies = [0];
            $status2xx = [0];
            $status3xx = [0];
            $status4xx = [0];
            $status5xx = [0];
        }

        return [
            'labels'       => $labels,
            'requests'     => $requests,
            'bandwidth_mb' => $bandwidthMb,
            'latency'      => $latencies,
            'status_2xx'   => $status2xx,
            'status_3xx'   => $status3xx,
            'status_4xx'   => $status4xx,
            'status_5xx'   => $status5xx,
        ];
    }

    /**
     * Get Top Bandwidth Breakdown by path.
     */
    protected function getTopBandwidthBreakdown($baseQuery, int $limit = 10): array
    {
        $rows = (clone $baseQuery)
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->select('path', DB::raw('SUM(bytes_sent) as total_bytes'))
            ->groupBy('path')
            ->orderByDesc('total_bytes')
            ->limit($limit)
            ->get();

        $maxBytes = $rows->max('total_bytes') ?: 1;

        $result = [];
        foreach ($rows as $row) {
            $bytes = (int) $row->total_bytes;
            $result[] = [
                'name'       => (string) $row->path,
                'count'      => $bytes,
                'count_fmt'  => $this->formatBytes($bytes),
                'percentage' => round(($bytes / $maxBytes) * 100, 1),
            ];
        }
        return $result;
    }

    /**
     * Get Top Breakdown for a column.
     */
    protected function getTopBreakdown($baseQuery, string $column, int $totalRequests, int $limit = 10): array
    {
        $rows = (clone $baseQuery)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->select($column, DB::raw('COUNT(*) as total_count'))
            ->groupBy($column)
            ->orderByDesc('total_count')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $val = (string) $row->{$column};
            $cnt = (int) $row->total_count;
            $pct = $totalRequests > 0 ? round(($cnt / $totalRequests) * 100, 1) : 0.0;

            $result[] = [
                'name'       => $val,
                'count'      => $cnt,
                'count_fmt'  => $this->formatNumber($cnt),
                'percentage' => $pct,
            ];
        }

        return $result;
    }

    /**
     * Resolve start and end cutoff times based on range.
     */
    public function resolveCutoffTime(string $range, ?string $customStart, ?string $customEnd): array
    {
        $now = now();
        $start = null;
        $end = null;

        switch ($range) {
            case '6h':
                $start = $now->copy()->subHours(6);
                break;
            case '12h':
                $start = $now->copy()->subHours(12);
                break;
            case '24h':
                $start = $now->copy()->subHours(24);
                break;
            case 'today':
                $start = $now->copy()->startOfDay();
                break;
            case '7d':
                $start = $now->copy()->subDays(7)->startOfDay();
                break;
            case '30d':
                $start = $now->copy()->subDays(30)->startOfDay();
                break;
            case 'custom':
                if ($customStart) {
                    try {
                        $start = Carbon::parse($customStart)->startOfDay();
                    } catch (\Throwable $e) {}
                }
                if ($customEnd) {
                    try {
                        $end = Carbon::parse($customEnd)->endOfDay();
                    } catch (\Throwable $e) {}
                }
                break;
            case 'all':
            default:
                $start = null;
                $end = null;
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    public function formatNumber(int $num): string
    {
        if ($num >= 1000000) {
            return round($num / 1000000, 2) . 'M';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'K';
        }
        return number_format($num);
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
