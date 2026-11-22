<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\AlertService;
use Nafiur\WebAnalytics\Services\CountryFlag;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;
use Nafiur\WebAnalytics\Services\PruneService;
use Nafiur\WebAnalytics\Services\TenantResolver;
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
     * Resolve the tenant used to scope dashboard data.
     *
     * Explicit tenant IDs are ignored unless an authorizer callback has been
     * configured. This prevents cross-tenant data exposure via query strings
     * or spoofable headers.
     */
    protected function resolveDashboardTenant(Request $request): ?string
    {
        $trusted = TenantResolver::isEnabled()
            ? TenantResolver::resolveTrusted($request)
            : null;

        $requested = $request->input('tenant_id');
        if (!is_scalar($requested) || $requested === '') {
            return $trusted;
        }

        $requested = (string) $requested;
        $authorizer = config('laravel-web-analytics.multi_tenancy.tenant_override_authorizer');

        try {
            if (is_callable($authorizer)) {
                if (call_user_func($authorizer, $requested, $trusted, $request) === true) {
                    return $requested;
                }
            } elseif (is_string($authorizer) && class_exists($authorizer)) {
                $instance = app($authorizer);
                $allowed = method_exists($instance, 'authorize')
                    ? $instance->authorize($requested, $trusted, $request)
                    : (is_callable($instance) ? $instance($requested, $trusted, $request) : false);
                if ($allowed === true) {
                    return $requested;
                }
            }
        } catch (\Throwable $e) {
            // Fail closed: never grant cross-tenant access on authorizer errors.
        }

        return $trusted;
    }

    /**
     * Display the Web Analytics Dashboard (legacy root URL).
     */
    public function index(Request $request)
    {
        return $this->renderDashboard($request, 'overview');
    }

    /**
     * Render the dashboard for a named sidebar section (bookmarkable URL).
     */
    public function section(Request $request)
    {
        return $this->renderDashboard($request, (string) $request->route('section', 'overview'));
    }

    /**
     * Map a sidebar section to its report pane and optional scroll target.
     *
     * @return array{0: string, 1: string|null, 2: string|null}
     */
    protected function sectionView(string $section): array
    {
        $panes = [
            'overview'    => ['#tab-overview', null],
            'realtime'    => ['#tab-live', null],
            'visitors'    => ['#tab-visitors', null],
            'sources'     => ['#tab-requests', '#section-traffic-sources'],
            'pages'       => ['#tab-requests', '#section-top-pages'],
            'geography'   => ['#tab-visitors', '#section-countries'],
            'devices'     => ['#tab-visitors', '#section-devices'],
            'campaigns'   => ['#tab-visitors', '#section-campaigns'],
            'events'      => ['#tab-unavailable', null],
            'conversions' => ['#tab-unavailable', null],
            'performance' => ['#tab-performance', null],
            'errors'      => ['#tab-security', null],
            'settings'    => ['#tab-settings', null],
        ];

        [$pane, $scroll] = $panes[$section] ?? $panes['overview'];
        $unavailable = in_array($section, ['events', 'conversions'], true)
            ? ($section === 'events' ? 'Events & Goals' : 'Conversions')
            : null;

        return [$pane, $scroll, $unavailable];
    }

    /**
     * Build and render the dashboard shell.
     */
    protected function renderDashboard(Request $request, string $section = 'overview')
    {
        [$activePane, $activeScroll, $unavailableTitle] = $this->sectionView($section);

        $timeRange = $request->input('range', '24h');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $source = $request->input('source', 'database'); // 'database' or 'file'
        $tenantId = $this->resolveDashboardTenant($request);

        if ($source === 'file') {
            $analytics = $this->logParser->getDashboardData($timeRange, $customStart, $customEnd);
            $logPath = $this->logParser->getLogPath();
            $logExists = $this->logParser->logFileExists();
            $logSize = $this->logParser->getLogFileSizeFormatted();
        } else {
            $analytics = $this->dbAnalytics->getDashboardData($timeRange, $customStart, $customEnd, $tenantId);
            $totalCount = LwaLogs::forTenant($tenantId)->count();
            $logPath = 'Database Table (' . config('laravel-web-analytics.table_name', 'lwa_logs') . ')';
            $logExists = true;
            $logSize = number_format($totalCount) . ' records';
        }

        $tableStats = [
            'rows'      => $totalCount ?? 0,
            'oldest'    => LwaLogs::forTenant($tenantId)->min('created_at'),
            'newest'    => LwaLogs::forTenant($tenantId)->max('created_at'),
        ];

        $serverSoftware = $request->server('SERVER_SOFTWARE') ?: ($_SERVER['SERVER_SOFTWARE'] ?? 'Web Server');
        $serverOs = PHP_OS_FAMILY . ' (' . php_uname('s') . ')';

        // Full IANA timezone database so the settings page can offer every
        // timezone supported by PHP without maintaining a hardcoded list.
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        sort($timezones);

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
            'source',
            'tenantId',
            'tableStats',
            'timezones',
            'section',
            'activePane',
            'activeScroll',
            'unavailableTitle'
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
        $tenantId = $this->resolveDashboardTenant($request);

        if ($source === 'file') {
            $analytics = $this->logParser->getDashboardData($timeRange, $customStart, $customEnd);
        } else {
            $analytics = $this->dbAnalytics->getDashboardData($timeRange, $customStart, $customEnd, $tenantId);
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

        $tenantId = $this->resolveDashboardTenant($request);

        // On reconnect EventSource sends Last-Event-ID; the query string only
        // carries the initial cursor rendered with the page.
        $lastId = (int) ($request->header('Last-Event-ID') ?: 0);
        if ($lastId <= 0) {
            $lastId = (int) $request->input('last_id', 0);
        }
        if ($lastId <= 0) {
            $lastId = (int) (LwaLogs::forTenant($tenantId)->max('id') ?? 0);
        }

        $interval = max(1, (int) config('laravel-web-analytics.stream_interval', 2));

        return response()->stream(function () use ($lastId, $tenantId, $interval) {
            $formatter = new DatabaseTrafficAnalytics();
            $activeWindow = max(1, (int) config('laravel-web-analytics.active_visitor_window', 5));
            $currentLastId = $lastId;

            // Each response is short-lived. EventSource handles pacing through
            // this retry value, so no PHP worker sleeps for tens of seconds.
            echo "retry: " . ($interval * 1000) . "\n\n";

            $newLogs = LwaLogs::forTenant($tenantId)
                ->where('id', '>', $currentLastId)
                ->orderBy('id', 'asc')
                ->limit(20)
                ->get();

            if ($newLogs->isNotEmpty()) {
                $formattedLogs = $newLogs->map(function ($log) use ($formatter) {
                    $isAuth = !empty($log->user_id);
                    $userName = $isAuth ? "User #{$log->user_id}" : 'Guest';
                    return [
                        'id'               => $log->id,
                        'tenant_id'        => $log->tenant_id,
                        'method'           => $log->method,
                        'path'             => $log->path,
                        'status_code'      => $log->status_code,
                        'response_time_ms' => $log->response_time_ms,
                        'bytes_sent_fmt'   => $formatter->formatBytes($log->bytes_sent),
                        'ip_address'       => $log->ip_address,
                        'browser'          => $log->browser,
                        'os'               => $log->os,
                        'device'           => $log->device,
                        'country'          => $log->country ?: 'Unknown',
                        'flag'             => CountryFlag::emoji($log->country),
                        'user_id'          => $log->user_id,
                        'user_name'        => $userName,
                        'is_auth'          => $isAuth,
                        'time_ago'         => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
                        'time_clock'       => $log->created_at ? $log->created_at->format('H:i:s') : '',
                        'created_at_iso'   => $log->created_at?->toIso8601String(),
                    ];
                });

                $currentLastId = $newLogs->last()->id;

                echo "id: {$currentLastId}\n";
                echo "event: traffic\n";
                echo "data: " . json_encode([
                    'logs'    => $formattedLogs,
                    'last_id' => $currentLastId,
                ]) . "\n\n";
            } else {
                echo "id: {$currentLastId}\n";
                echo "event: ping\n";
                echo "data: " . json_encode([
                    'time'    => now()->toIso8601String(),
                    'last_id' => $currentLastId,
                ]) . "\n\n";
            }

            // Stats are emitted on every response so the live visitor count
            // also decays when no new traffic is arriving.
            $liveVisitors = LwaLogs::forTenant($tenantId)
                ->where('created_at', '>=', now()->subMinutes($activeWindow))
                ->distinct()
                ->count(DB::raw('COALESCE(session_id, ip_address)'));
            $totalCount = LwaLogs::forTenant($tenantId)->count();

            echo "id: {$currentLastId}\n";
            echo "event: stats\n";
            echo "data: " . json_encode([
                'live_visitors'  => $liveVisitors,
                'total_requests' => $totalCount,
            ]) . "\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
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
        $tenantId = $this->resolveDashboardTenant($request);

        $cutoff = (new DatabaseTrafficAnalytics())->resolveCutoffTime($timeRange, $request->input('start_date'), $request->input('end_date'));
        $query = LwaLogs::forTenant($tenantId);
        if ($cutoff['start']) {
            $query->where('created_at', '>=', $cutoff['start']);
        }
        if ($cutoff['end']) {
            $query->where('created_at', '<=', $cutoff['end']);
        }

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['ID', 'Tenant ID', 'IP Address', 'Method', 'URL / Path', 'Status Code', 'Response Time (ms)', 'Browser', 'Device', 'OS', 'Country', 'City', 'User ID', 'Date Time'];

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

            $query->chunkById(500, function ($logs) use ($file, $sanitize) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $sanitize($log->tenant_id ?: '-'),
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

    /**
     * Prune old telemetry rows from the settings page.
     */
    public function prune(Request $request, PruneService $prune)
    {
        if (!config('laravel-web-analytics.settings.destructive_actions_enabled', true)) {
            abort(403);
        }

        $days = max(1, min(3650, (int) $request->input('days', 30)));
        $tenantId = $this->resolveDashboardTenant($request);

        $deleted = $prune->prune($days, $tenantId);

        return back()->with('lwa_status', "Pruned {$deleted} record(s) older than {$days} days.");
    }

    /**
     * Empty the telemetry table (optionally scoped to the current tenant).
     */
    public function emptyTable(Request $request)
    {
        if (!config('laravel-web-analytics.settings.destructive_actions_enabled', true)) {
            abort(403);
        }

        if ($request->input('confirm') !== 'DELETE') {
            return back()->withErrors(['confirm' => 'Type DELETE to confirm this action.']);
        }

        $tenantId = $this->resolveDashboardTenant($request);
        $query = LwaLogs::forTenant($tenantId);
        $count = $query->count();
        $query->delete();

        return back()->with('lwa_status', "Deleted {$count} telemetry record(s).");
    }

    /**
     * Send a test notification through the configured alert channel.
     */
    public function testAlert()
    {
        if (!config('laravel-web-analytics.alerts.enabled')) {
            return back()->withErrors(['alert' => 'Alerts are disabled in configuration.']);
        }

        AlertService::sendTest();

        return back()->with('lwa_status', 'Test alert sent.');
    }
}
