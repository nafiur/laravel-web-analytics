<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class WebAnalyticsLogParser
{
    protected ?string $logPath = null;
    protected int $maxLines = 50000;

    public function __construct(?string $logPath = null)
    {
        $this->logPath = $logPath ?: $this->detectLogPath();
        $this->maxLines = (int) config('laravel-web-analytics.max_lines', config('server_analytics.max_lines', 50000));
    }

    /**
     * Auto-detect web server access log path across environments (Laragon, Nginx, Apache, Linux).
     */
    public function detectLogPath(): ?string
    {
        // 1. Explicitly configured path from config or .env
        $configured = config('laravel-web-analytics.server_log_path', config('server_analytics.log_path'));
        if ($configured && file_exists($configured) && is_readable($configured)) {
            return $configured;
        }

        // 2. Standard server log locations (Laravel app storage, Nginx, Apache, LiteSpeed)
        $candidates = [
            function_exists('storage_path') ? storage_path('logs/access.log') : null,
            '/var/log/nginx/access.log',
            '/var/log/apache2/access.log',
            '/var/log/httpd/access_log',
            '/usr/local/lsws/logs/access.log',
            '/var/log/lsws/access.log',
            '/www/server/lsws/logs/access.log',
            '/etc/apache2/logs/access_log',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function getLogPath(): ?string
    {
        return $this->logPath;
    }

    public function logFileExists(): bool
    {
        return $this->logPath && file_exists($this->logPath) && is_readable($this->logPath);
    }

    public function getLogFileSizeFormatted(): string
    {
        if (!$this->logFileExists()) {
            return '0 B';
        }
        $bytes = filesize($this->logPath);
        return $this->formatBytes($bytes);
    }

    /**
     * Read the last N lines of a file backwards efficiently.
     */
    public function readTailLines(int $lines = 20000): array
    {
        if (!$this->logFileExists()) {
            return [];
        }

        $filePath = $this->logPath;
        $file = fopen($filePath, 'rb');
        if (!$file) {
            return [];
        }

        $bufferSize = 8192;
        $linesArr = [];
        $leftover = '';

        fseek($file, 0, SEEK_END);
        $fileSize = ftell($file);
        $pos = $fileSize;

        while ($pos > 0 && count($linesArr) < $lines) {
            $readSize = min($bufferSize, $pos);
            $pos -= $readSize;
            fseek($file, $pos);
            $chunk = fread($file, $readSize);

            $chunk .= $leftover;
            $split = explode("\n", $chunk);

            $leftover = array_shift($split); // first partial line

            for ($i = count($split) - 1; $i >= 0; $i--) {
                $trimmed = trim($split[$i]);
                if ($trimmed !== '') {
                    $linesArr[] = $trimmed;
                    if (count($linesArr) >= $lines) {
                        break;
                    }
                }
            }
        }

        if ($leftover !== '' && count($linesArr) < $lines) {
            $trimmed = trim($leftover);
            if ($trimmed !== '') {
                $linesArr[] = $trimmed;
            }
        }

        fclose($file);
        return array_reverse($linesArr);
    }

    /**
     * Parse raw log lines into structured records.
     */
    public function parseRecords(?string $timeRange = '24h', ?string $customStart = null, ?string $customEnd = null): array
    {
        $rawLines = $this->readTailLines($this->maxLines);
        if (empty($rawLines)) {
            return [];
        }

        $cutoff = $this->resolveCutoffTime($timeRange, $customStart, $customEnd);
        $startDate = $cutoff['start'];
        $endDate = $cutoff['end'];

        $records = [];

        // Common & Combined Log Format Regex:
        // Example: 127.0.0.1 - - [07/Oct/2026:13:40:01 +0600] "GET /path HTTP/1.1" 200 45023 "http://ref.com" "Mozilla/5.0..."
        $pattern = '/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+|\S+)\s+([^"\s]+)?(?:\s+([^"]+))?"\s+(\d{3})\s+(\d+|-)(?:\s+"([^"]*)"\s+"([^"]*)")?/';

        foreach ($rawLines as $line) {
            if (!preg_match($pattern, $line, $m)) {
                continue;
            }

            $ip         = $m[1] ?? '127.0.0.1';
            $timeStr    = $m[2] ?? '';
            $method     = strtoupper($m[3] ?? 'GET');
            $rawPath    = $m[4] ?? '/';
            $protocol   = $m[5] ?? 'HTTP/1.1';
            $statusCode = (int) ($m[6] ?? 200);
            $bytes      = ($m[7] === '-' || empty($m[7])) ? 0 : (int) $m[7];
            $referrer   = $m[8] ?? null;
            $userAgent  = $m[9] ?? null;

            // Parse Date: 07/Oct/2026:13:40:01 +0600
            try {
                $dt = Carbon::createFromFormat('d/M/Y:H:i:s O', $timeStr);
            } catch (\Throwable $e) {
                try {
                    $dt = Carbon::parse(preg_replace('/:[0-9]{2}:[0-9]{2}:[0-9]{2}/', ' ', $timeStr));
                } catch (\Throwable $e2) {
                    $dt = now();
                }
            }

            // Filter date range
            if ($startDate && $dt->lt($startDate)) {
                continue;
            }
            if ($endDate && $dt->gt($endDate)) {
                continue;
            }

            // Clean path (strip query strings for grouping if desired, but keep path clean)
            $parsedUrl = parse_url($rawPath);
            $cleanPath = $parsedUrl['path'] ?? $rawPath;
            if (empty($cleanPath)) {
                $cleanPath = '/';
            }

            $uaInfo = $this->parseUserAgent($userAgent);

            $records[] = [
                'ip'          => $ip,
                'datetime'    => $dt,
                'timestamp'   => $dt->timestamp,
                'time_label'  => $dt->format('Y-m-d H:00'),
                'date_label'  => $dt->format('Y-m-d'),
                'method'      => $method,
                'path'        => $cleanPath,
                'full_path'   => $rawPath,
                'status'      => $statusCode,
                'status_group'=> (string) (floor($statusCode / 100) . 'xx'),
                'bytes'       => $bytes,
                'referrer'    => ($referrer && $referrer !== '-') ? $referrer : 'Direct',
                'user_agent'  => $userAgent,
                'browser'     => $uaInfo['browser'],
                'os'          => $uaInfo['os'],
                'device'      => $uaInfo['device'],
                'country'     => $this->resolveCountry($ip),
            ];
        }

        return $records;
    }

    /**
     * Compute full dashboard analytics data from parsed records.
     */
    public function getDashboardData(string $timeRange = '24h', ?string $customStart = null, ?string $customEnd = null): array
    {
        $records = $this->parseRecords($timeRange, $customStart, $customEnd);
        $totalRequests = count($records);

        if ($totalRequests === 0) {
            return $this->getEmptyDashboardData($timeRange);
        }

        $totalBytes = 0;
        $uniqueIps = [];
        $statusCounts = ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0, 'other' => 0];
        $detailedStatusCodes = [];
        $pathsCount = [];
        $browsersCount = [];
        $osCount = [];
        $devicesCount = [];
        $countriesCount = [];
        $userAgentsCount = [];
        $referrersCount = [];
        $methodsCount = [];

        // Time Series Buckets (by hour or by day depending on range)
        $isHourly = in_array($timeRange, ['24h', 'today', '12h', '6h']);
        $timeSeriesBuckets = [];

        foreach ($records as $r) {
            $totalBytes += $r['bytes'];
            $uniqueIps[$r['ip']] = ($uniqueIps[$r['ip']] ?? 0) + 1;

            // Status group
            $stGroup = $r['status_group'];
            if (isset($statusCounts[$stGroup])) {
                $statusCounts[$stGroup]++;
            } else {
                $statusCounts['other']++;
            }

            // Detailed Status
            $stCode = (string) $r['status'];
            $detailedStatusCodes[$stCode] = ($detailedStatusCodes[$stCode] ?? 0) + 1;

            // Path
            $pathsCount[$r['path']] = ($pathsCount[$r['path']] ?? 0) + 1;

            // Browser
            $browsersCount[$r['browser']] = ($browsersCount[$r['browser']] ?? 0) + 1;

            // OS
            $osCount[$r['os']] = ($osCount[$r['os']] ?? 0) + 1;

            // Device
            $devicesCount[$r['device']] = ($devicesCount[$r['device']] ?? 0) + 1;

            // Country
            $countriesCount[$r['country']] = ($countriesCount[$r['country']] ?? 0) + 1;

            // User Agent
            $uaShort = $r['user_agent'] ? substr($r['user_agent'], 0, 80) : 'None';
            $userAgentsCount[$uaShort] = ($userAgentsCount[$uaShort] ?? 0) + 1;

            // Referrer
            $referrersCount[$r['referrer']] = ($referrersCount[$r['referrer']] ?? 0) + 1;

            // Method
            $methodsCount[$r['method']] = ($methodsCount[$r['method']] ?? 0) + 1;

            // Time Bucket
            $bucketKey = $isHourly ? $r['datetime']->format('Y-m-d H:00') : $r['datetime']->format('Y-m-d');
            if (!isset($timeSeriesBuckets[$bucketKey])) {
                $timeSeriesBuckets[$bucketKey] = [
                    'label'     => $isHourly ? $r['datetime']->format('H:i') : $r['datetime']->format('d M'),
                    'full_time' => $bucketKey,
                    'requests'  => 0,
                    'bytes'     => 0,
                    '2xx'       => 0,
                    '3xx'       => 0,
                    '4xx'       => 0,
                    '5xx'       => 0,
                ];
            }
            $timeSeriesBuckets[$bucketKey]['requests']++;
            $timeSeriesBuckets[$bucketKey]['bytes'] += $r['bytes'];
            if (isset($timeSeriesBuckets[$bucketKey][$stGroup])) {
                $timeSeriesBuckets[$bucketKey][$stGroup]++;
            }
        }

        // Sort time series chronologically
        ksort($timeSeriesBuckets);

        $errorCount = ($statusCounts['4xx'] ?? 0) + ($statusCounts['5xx'] ?? 0);
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0;
        $successRate = $totalRequests > 0 ? round((($statusCounts['2xx'] ?? 0) / $totalRequests) * 100, 2) : 0;

        // Sparklines data (last 12-24 points)
        $sparklineRequests = array_values(array_column($timeSeriesBuckets, 'requests'));
        $sparklineBytes = array_values(array_map(fn($b) => round($b / (1024 * 1024), 2), array_column($timeSeriesBuckets, 'bytes')));

        // Recent Logs (latest 20)
        $recentLogs = [];
        $reversed = array_reverse($records);
        foreach (array_slice($reversed, 0, 20) as $idx => $r) {
            $recentLogs[] = [
                'id'               => $idx + 1,
                'method'           => $r['method'],
                'path'             => $r['path'],
                'status_code'      => $r['status'],
                'response_time_ms' => 0,
                'bytes_sent_fmt'   => $this->formatBytes($r['bytes']),
                'ip_address'       => $r['ip'],
                'browser'          => $r['browser'],
                'os'               => $r['os'],
                'device'           => $r['device'],
                'country'          => $r['country'],
                'city'             => null,
                'user_id'          => null,
                'user_name'        => 'Guest',
                'is_auth'          => false,
                'time_ago'         => $r['datetime']->diffForHumans(),
                'time_clock'       => $r['datetime']->format('H:i:s'),
            ];
        }

        // Top Error Paths & Recent Error Logs
        $errorRecords = array_values(array_filter($records, fn($r) => $r['status'] >= 400));
        $topErrorsGrouped = [];
        foreach ($errorRecords as $er) {
            $k = $er['method'] . '|' . $er['status'] . '|' . $er['path'];
            if (!isset($topErrorsGrouped[$k])) {
                $topErrorsGrouped[$k] = [
                    'path'        => $er['path'],
                    'status_code' => $er['status'],
                    'method'      => $er['method'],
                    'count'       => 0,
                    'last_seen'   => $er['datetime'],
                ];
            }
            $topErrorsGrouped[$k]['count']++;
            if ($er['datetime']->gt($topErrorsGrouped[$k]['last_seen'])) {
                $topErrorsGrouped[$k]['last_seen'] = $er['datetime'];
            }
        }
        usort($topErrorsGrouped, fn($a, $b) => $b['count'] <=> $a['count']);
        $topErrorPaths = array_map(function ($row) {
            return [
                'path'           => $row['path'],
                'status_code'    => $row['status_code'],
                'method'         => $row['method'],
                'count'          => $row['count'],
                'count_fmt'      => number_format($row['count']),
                'last_seen'      => $row['last_seen']->diffForHumans(),
                'last_seen_time' => $row['last_seen']->format('Y-m-d H:i:s'),
            ];
        }, array_slice($topErrorsGrouped, 0, 20));

        $recentErrorLogs = [];
        $reversedErrors = array_reverse($errorRecords);
        foreach (array_slice($reversedErrors, 0, 30) as $idx => $er) {
            $recentErrorLogs[] = [
                'id'               => $idx + 1,
                'method'           => $er['method'],
                'path'             => $er['path'],
                'status_code'      => $er['status'],
                'response_time_ms' => 0,
                'bytes_sent_fmt'   => $this->formatBytes($er['bytes']),
                'ip_address'       => $er['ip'],
                'browser'          => $er['browser'],
                'os'               => $er['os'],
                'device'           => $er['device'],
                'country'          => $er['country'],
                'referrer'         => $er['referrer'],
                'user_id'          => null,
                'user_name'        => 'Guest',
                'is_auth'          => false,
                'time_ago'         => $er['datetime']->diffForHumans(),
                'time_clock'       => $er['datetime']->format('H:i:s'),
            ];
        }

        return [
            'summary' => [
                'total_requests'       => $totalRequests,
                'total_requests_fmt'   => $this->formatNumber($totalRequests),
                'total_bytes'          => $totalBytes,
                'total_bytes_fmt'      => $this->formatBytes($totalBytes),
                'unique_visitors'      => count($uniqueIps),
                'unique_visitors_fmt'  => $this->formatNumber(count($uniqueIps)),
                'live_visitors'        => max(1, count($uniqueIps)),
                'live_visitors_fmt'    => number_format(max(1, count($uniqueIps))),
                'live_requests_5m'     => min($totalRequests, 20),
                'avg_response_time'    => 0,
                'error_rate'           => $errorRate,
                'error_count'          => $errorCount,
                'error_count_fmt'      => number_format($errorCount),
                'success_rate'         => $successRate,
                'status_counts'        => $statusCounts,
                'time_range'           => $timeRange,
                'log_file'             => $this->logPath ?: 'Web Server Log',
                'log_size'             => $this->getLogFileSizeFormatted(),
                'storage_type'         => 'Server Log File',
            ],
            'time_series' => [
                'labels'    => array_values(array_column($timeSeriesBuckets, 'label')),
                'requests'  => array_values(array_column($timeSeriesBuckets, 'requests')),
                'bandwidth_mb' => array_values(array_map(fn($b) => round($b / (1024 * 1024), 2), array_column($timeSeriesBuckets, 'bytes'))),
                'status_2xx'=> array_values(array_column($timeSeriesBuckets, '2xx')),
                'status_3xx'=> array_values(array_column($timeSeriesBuckets, '3xx')),
                'status_4xx'=> array_values(array_column($timeSeriesBuckets, '4xx')),
                'status_5xx'=> array_values(array_column($timeSeriesBuckets, '5xx')),
            ],
            'sparklines' => [
                'requests' => $sparklineRequests,
                'bytes'    => $sparklineBytes,
            ],
            'breakdowns' => [
                'top_paths'         => $this->formatBreakdownList($pathsCount, $totalRequests, 12),
                'top_ips'           => $this->formatBreakdownList($uniqueIps, $totalRequests, 10),
                'top_countries'     => $this->formatBreakdownList($countriesCount, $totalRequests, 10),
                'top_browsers'      => $this->formatBreakdownList($browsersCount, $totalRequests, 8),
                'top_os'            => $this->formatBreakdownList($osCount, $totalRequests, 8),
                'top_devices'       => $this->formatBreakdownList($devicesCount, $totalRequests, 5),
                'top_status_codes'  => $this->formatBreakdownList($detailedStatusCodes, $totalRequests, 8),
                'top_referrers'     => $this->formatBreakdownList($referrersCount, $totalRequests, 8),
                'top_user_agents'   => $this->formatBreakdownList($userAgentsCount, $totalRequests, 8),
            ],
            'recent_logs'       => $recentLogs,
            'top_error_paths'   => $topErrorPaths,
            'recent_error_logs' => $recentErrorLogs,
        ];
    }

    protected function formatBreakdownList(array $items, int $total, int $limit = 10): array
    {
        arsort($items);
        $slice = array_slice($items, 0, $limit, true);
        $result = [];

        foreach ($slice as $name => $count) {
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $result[] = [
                'name'       => (string) $name,
                'count'      => $count,
                'count_fmt'  => $this->formatNumber($count),
                'percentage' => $pct,
            ];
        }

        return $result;
    }

    protected function resolveCutoffTime(string $range, ?string $customStart, ?string $customEnd): array
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
            case 'today':
                $start = $now->copy()->startOfDay();
                break;
            case '24h':
                $start = $now->copy()->subHours(24);
                break;
            case '7d':
                $start = $now->copy()->subDays(7);
                break;
            case '30d':
                $start = $now->copy()->subDays(30);
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
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    protected function parseUserAgent(?string $ua): array
    {
        if (!$ua || $ua === '-') {
            return ['browser' => 'Direct / API', 'os' => 'Unknown', 'device' => 'Desktop'];
        }

        // Detect Bot
        if (preg_match('/(googlebot|bingbot|yandex|duckduckbot|baiduspider|slurp|facebookexternalhit|twitterbot|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest\/0\.|pinterestbot|slackbot|vkShare|W3C_Validator|whatsapp)/i', $ua, $botM)) {
            return ['browser' => 'Bot: ' . ucfirst(strtolower($botM[1])), 'os' => 'Bot', 'device' => 'Bot'];
        }

        // Detect Device
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|mobile)/i', $ua)) {
            $device = 'Mobile';
        }

        // Detect OS
        $os = 'Unknown';
        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/ubuntu/i', $ua)) {
            $os = 'Ubuntu';
        }

        // Detect Browser
        $browser = 'Unknown';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $ua) && !preg_match('/edg/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/postman/i', $ua)) {
            $browser = 'Postman';
        } elseif (preg_match('/curl/i', $ua)) {
            $browser = 'Curl';
        }

        return [
            'browser' => $browser,
            'os'      => $os,
            'device'  => $device,
        ];
    }

    protected function resolveCountry(string $ip): string
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.')) {
            return 'Localhost / Internal';
        }

        // Basic GeoIP lookup heuristic for typical server subnets
        if (str_starts_with($ip, '103.') || str_starts_with($ip, '119.') || str_starts_with($ip, '180.') || str_starts_with($ip, '202.')) {
            return 'Bangladesh';
        }
        if (str_starts_with($ip, '35.') || str_starts_with($ip, '34.') || str_starts_with($ip, '52.') || str_starts_with($ip, '54.') || str_starts_with($ip, '142.')) {
            return 'United States';
        }
        if (str_starts_with($ip, '13.') || str_starts_with($ip, '20.')) {
            return 'Singapore';
        }

        return 'Global / Other';
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function formatNumber(int|float $num): string
    {
        if ($num >= 1000000) {
            return round($num / 1000000, 2) . 'M';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'k';
        }
        return number_format($num);
    }

    protected function getEmptyDashboardData(string $timeRange): array
    {
        return [
            'summary' => [
                'total_requests'       => 0,
                'total_requests_fmt'   => '0',
                'total_bytes'          => 0,
                'total_bytes_fmt'      => '0 B',
                'unique_visitors'      => 0,
                'unique_visitors_fmt'  => '0',
                'live_visitors'        => 0,
                'live_visitors_fmt'    => '0',
                'live_requests_5m'     => 0,
                'avg_response_time'    => 0,
                'error_rate'           => 0,
                'error_count'          => 0,
                'error_count_fmt'      => '0',
                'success_rate'         => 0,
                'status_counts'        => ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0, 'other' => 0],
                'time_range'           => $timeRange,
                'log_file'             => $this->logPath ?: 'Not Found',
                'log_size'             => '0 B',
                'storage_type'         => 'Server Log File',
            ],
            'time_series' => [
                'labels'     => [],
                'requests'   => [],
                'bandwidth_mb' => [],
                'status_2xx' => [],
                'status_3xx' => [],
                'status_4xx' => [],
                'status_5xx' => [],
            ],
            'sparklines' => [
                'requests' => [],
                'bytes'    => [],
            ],
            'breakdowns' => [
                'top_paths'        => [],
                'top_ips'          => [],
                'top_countries'    => [],
                'top_browsers'     => [],
                'top_os'           => [],
                'top_devices'      => [],
                'top_status_codes' => [],
                'top_referrers'    => [],
                'top_user_agents'  => [],
            ],
            'recent_logs'       => [],
            'top_error_paths'   => [],
            'recent_error_logs' => [],
        ];
    }
}

