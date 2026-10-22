<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;
use Nafiur\WebAnalytics\Services\WebAnalyticsLogParser;

class WebAnalyticsController extends Controller
{
    protected DatabaseTrafficAnalytics $dbAnalytics;
    protected WebAnalyticsLogParser $logParser;

    public function __construct()
    {
        $this->dbAnalytics = new DatabaseTrafficAnalytics();
        $this->logParser = new WebAnalyticsLogParser();
    }

    /**
     * Display the Web Analytics Dashboard.
     */
    public function index(Request $request)
    {
        $timeRange = $request->input('range', '24h');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $source = $request->input('source', 'database'); // 'database' or 'file'

        if ($source === 'file') {
            $analytics = $this->logParser->getDashboardData($timeRange, $customStart, $customEnd);
            $logPath = $this->logParser->getLogPath();
            $logExists = $this->logParser->logFileExists();
            $logSize = $this->logParser->getLogFileSizeFormatted();
        } else {
            $analytics = $this->dbAnalytics->getDashboardData($timeRange, $customStart, $customEnd);
            $totalCount = LwaLogs::count();
            $logPath = 'Database Table (' . config('laravel-web-analytics.table_name', 'lwa_logs') . ')';
            $logExists = true;
            $logSize = number_format($totalCount) . ' records';
        }

        $serverSoftware = $request->server('SERVER_SOFTWARE') ?: ($_SERVER['SERVER_SOFTWARE'] ?? 'Web Server');
        $serverOs = PHP_OS_FAMILY . ' (' . php_uname('s') . ')';

        return view('web-analytics::dashboard', compact(
            'analytics',
            'timeRange',
            'customStart',
            'customEnd',
            'logPath',
            'logExists',
            'logSize',
            'serverSoftware',
            'serverOs',
            'source'
        ));
    }

    /**
     * AJAX endpoint for real-time / dynamic filter updates.
     */
    public function data(Request $request)
    {
        $timeRange = $request->input('range', '24h');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $source = $request->input('source', 'database');

        if ($source === 'file') {
            $analytics = $this->logParser->getDashboardData($timeRange, $customStart, $customEnd);
        } else {
            $analytics = $this->dbAnalytics->getDashboardData($timeRange, $customStart, $customEnd);
        }

        return response()->json([
            'success'   => true,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Server-Sent Events (SSE) Stream for Instant Real-Time Web Traffic.
     */
    public function stream(Request $request)
    {
        // Close session immediately so other concurrent user requests aren't blocked by PHP session locks
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $lastId = (int) $request->input('last_id', 0);
        if ($lastId <= 0) {
            $lastId = (int) (LwaLogs::max('id') ?? 0);
        }

        return response()->stream(function () use ($lastId) {
            $currentLastId = $lastId;
            $iterations = 0;
            $maxIterations = 25; // Stream for ~25s, then EventSource reconnects cleanly

            while ($iterations < $maxIterations) {
                if (connection_aborted()) {
                    break;
                }

                // Check for new incoming requests - no User model query
                $newLogs = LwaLogs::where('id', '>', $currentLastId)
                    ->orderBy('id', 'asc')
                    ->limit(20)
                    ->get();

                if ($newLogs->isNotEmpty()) {
                    $formattedLogs = $newLogs->map(function ($log) {
                        $isAuth = !empty($log->user_id);
                        $userName = $isAuth ? "User #{$log->user_id}" : 'Guest';
                        return [
                            'id'               => $log->id,
                            'method'           => $log->method,
                            'path'             => $log->path,
                            'status_code'      => $log->status_code,
                            'response_time_ms' => $log->response_time_ms,
                            'bytes_sent_fmt'   => (new DatabaseTrafficAnalytics())->formatBytes($log->bytes_sent),
                            'ip_address'       => $log->ip_address,
                            'browser'          => $log->browser,
                            'os'               => $log->os,
                            'device'           => $log->device,
                            'country'          => $log->country ?: 'Unknown',
                            'user_id'          => $log->user_id,
                            'user_name'        => $userName,
                            'is_auth'          => $isAuth,
                            'time_ago'         => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
                            'time_clock'       => $log->created_at ? $log->created_at->format('H:i:s') : '',
                        ];
                    });

                    $currentLastId = $newLogs->last()->id;

                    echo "event: traffic\n";
                    echo "data: " . json_encode([
                        'logs'    => $formattedLogs,
                        'last_id' => $currentLastId,
                    ]) . "\n\n";

                    // Send updated live stats (Hybrid Session-based)
                    $liveVisitors = LwaLogs::where('created_at', '>=', now()->subMinutes(config('laravel-web-analytics.active_visitor_window', 5)))
                        ->distinct()
                        ->count(DB::raw('COALESCE(session_id, ip_address)'));
                    $totalCount = LwaLogs::count();

                    echo "event: stats\n";
                    echo "data: " . json_encode([
                        'live_visitors'  => max(1, $liveVisitors),
                        'total_requests' => $totalCount,
                    ]) . "\n\n";
                } else {
                    // Lightweight keepalive ping
                    echo "event: ping\n";
                    echo "data: {\"time\":\"" . now()->toIso8601String() . "\",\"last_id\":{$currentLastId}}\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                sleep((int) config('laravel-web-analytics.stream_interval', 2));
                $iterations++;
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-transform',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Export analytics data to CSV format.
     */
    public function export(Request $request)
    {
        $timeRange = $request->input('range', '24h');
        $fileName = 'web_analytics_report_' . date('Y-m-d_His') . '.csv';

        $cutoff = (new DatabaseTrafficAnalytics())->resolveCutoffTime($timeRange, $request->input('start_date'), $request->input('end_date'));
        $query = LwaLogs::query();
        if ($cutoff['start']) {
            $query->where('created_at', '>=', $cutoff['start']);
        }
        if ($cutoff['end']) {
            $query->where('created_at', '<=', $cutoff['end']);
        }

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['ID', 'IP Address', 'Method', 'URL / Path', 'Status Code', 'Response Time (ms)', 'Browser', 'Device', 'OS', 'Country', 'City', 'User ID', 'Date Time'];

        $sanitize = function ($value) {
            if ($value === null || $value === '') {
                return '';
            }
            $str = (string) $value;
            // Prevent CSV / Excel formula injection (CWE-1236)
            if (isset($str[0]) && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
                return "'" . $str;
            }
            return $str;
        };

        $callback = function () use ($query, $columns, $sanitize) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $query->chunk(500, function ($logs) use ($file, $sanitize) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $sanitize($log->ip_address),
                        $sanitize($log->method),
                        $sanitize($log->path),
                        (int) $log->status_code,
                        (float) $log->response_time_ms,
                        $sanitize($log->browser),
                        $sanitize($log->device),
                        $sanitize($log->os),
                        $sanitize($log->country ?: 'Unknown'),
                        $sanitize($log->city),
                        $sanitize($log->user_id ? "User #{$log->user_id}" : 'Guest'),
                        $log->created_at,
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
