<?php

namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\AlertService;
use Nafiur\WebAnalytics\Services\GeoIpService;
use Symfony\Component\HttpFoundation\Response;

class TrackWebTraffic
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('laravel-web-analytics.enabled', true)) {
            $request->attributes->set('_lwa_start_time', microtime(true));
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (!config('laravel-web-analytics.enabled', true)) {
            return;
        }

        try {
            $path = '/' . ltrim($request->path(), '/');

            // Skip ignored paths or static assets
            if ($this->shouldIgnorePath($path)) {
                return;
            }

            // Client IP resolution & optional anonymization
            $ip = $this->resolveClientIp($request);
            if (config('laravel-web-analytics.anonymize_ip', false)) {
                $ip = $this->anonymizeIp($ip);
            }

            // User Agent parsing
            $userAgent = $request->userAgent() ?? '';
            $uaInfo = $this->parseUserAgent($userAgent);

            // Check capture bots setting
            $isBot = ($uaInfo['device'] === 'Bot' || $uaInfo['browser'] === 'Bot / Crawler');
            if ($isBot && !config('laravel-web-analytics.capture.bots', false)) {
                return;
            }

            // Check capture API / AJAX settings
            if (($request->is('api/*') || $request->expectsJson()) && !config('laravel-web-analytics.capture.api', true)) {
                return;
            }
            if ($request->ajax() && !config('laravel-web-analytics.capture.ajax', true)) {
                return;
            }

            $startTime = $request->attributes->get('_lwa_start_time', defined('LARAVEL_START') ? constant('LARAVEL_START') : microtime(true));
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            // Response byte size calculation
            $bytes = 0;
            if ($response->headers->has('Content-Length')) {
                $bytes = (int) $response->headers->get('Content-Length');
            } else {
                $content = $response->getContent();
                $bytes = is_string($content) ? strlen($content) : 0;
            }

            // Resolve location (Country & City)
            $location = $this->resolveLocation($request, $ip);

            // Capture authenticated user ID without depending on any User model
            $userId = null;
            try {
                $userId = $request->user()?->getAuthIdentifier() 
                    ?? (function_exists('auth') ? auth()->id() ?? auth('web')->id() : null);
            } catch (\Throwable $e) {
                // Ignore auth inspection errors
            }

            // Hybrid Session Identifier:
            // 1. Authenticated: 'u_' . $userId
            // 2. Guest with Laravel session: 's_' . sessionId
            // 3. Stateless: 'f_' . md5(ip|ua)
            $sessionId = null;
            try {
                if ($userId) {
                    $sessionId = 'u_' . $userId;
                } elseif ($request->hasSession()) {
                    $laravelSessionId = $request->session()->getId();
                    if ($laravelSessionId) {
                        $sessionId = 's_' . substr($laravelSessionId, 0, 40);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore session retrieval issues
            }

            if (!$sessionId) {
                $sessionId = 'f_' . substr(md5($ip . '|' . $userAgent), 0, 24);
            }

            // Referrer
            $rawReferrer = $request->headers->get('referer');
            $referrer = $rawReferrer ? substr($rawReferrer, 0, 490) : 'Direct';

            // Alert on server errors (5xx)
            if ($response->getStatusCode() >= 500) {
                AlertService::notifyErrorSpike($response->getStatusCode(), $request->fullUrl(), $ip);
            }

            LwaLogs::create([
                'session_id'       => substr($sessionId, 0, 100),
                'ip_address'       => substr($ip, 0, 45),
                'method'           => strtoupper($request->method()),
                'path'             => substr($path, 0, 490),
                'full_url'         => substr($request->fullUrl(), 0, 1000),
                'status_code'      => $response->getStatusCode(),
                'response_time_ms' => $durationMs,
                'bytes_sent'       => $bytes,
                'country'          => $location['country'],
                'city'             => $location['city'],
                'browser'          => $uaInfo['browser'],
                'os'               => $uaInfo['os'],
                'device'           => $uaInfo['device'],
                'referrer'         => $referrer,
                'user_agent'       => substr($userAgent, 0, 500),
                'user_id'          => $userId,
            ]);
        } catch (\Throwable $e) {
            // Never disrupt user request due to logging failures
        }
    }

    /**
     * Check if the path should be ignored.
     */
    protected function shouldIgnorePath(string $path): bool
    {
        $trimmed = ltrim($path, '/');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $ignoredExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'webp', 'avif'];
        if ($ext && in_array($ext, $ignoredExtensions)) {
            return true;
        }

        $ignoredPaths = config('laravel-web-analytics.ignored_paths', [
            'livewire/*',
            'broadcasting/auth',
            'web-analytics*',
            'admin/web-analytics*',
            'admin/server-analytics*',
            'horizon/*',
            'telescope/*',
            'pulse/*',
            'log-viewer*',
        ]);

        foreach ($ignoredPaths as $pattern) {
            if (Str::is($pattern, $trimmed) || Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get real client IP address.
     */
    protected function resolveClientIp(Request $request): string
    {
        if ($cfIp = $request->server('HTTP_CF_CONNECTING_IP')) {
            return $cfIp;
        }
        if ($xRealIp = $request->server('HTTP_X_REAL_IP')) {
            return $xRealIp;
        }
        if ($forwarded = $request->server('HTTP_X_FORWARDED_FOR')) {
            $parts = explode(',', $forwarded);
            return trim($parts[0]);
        }
        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Anonymize IP address for GDPR compliance.
     */
    protected function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('~\.\d+$~', '.0', $ip) ?? $ip;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return preg_replace('~:[0-9a-fA-F]+$~', ':0000', $ip) ?? $ip;
        }
        return $ip;
    }

    /**
     * Resolve location information (Country and City).
     */
    protected function resolveLocation(Request $request, string $ip): array
    {
        // 1. Cloudflare headers
        if ($cfCountry = $request->server('HTTP_CF_IPCOUNTRY')) {
            return [
                'country' => strtoupper($cfCountry),
                'city'    => $request->server('HTTP_CF_IPCITY'),
            ];
        }

        // 2. Localhost / Private IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.16.')) {
            return [
                'country' => 'Localhost / Internal',
                'city'    => 'Local',
            ];
        }

        // 3. GeoIP lookup service (if enabled)
        if (config('laravel-web-analytics.geoip.enabled', true)) {
            $geo = GeoIpService::resolve($ip);
            if (!empty($geo['country_name']) || !empty($geo['country_code'])) {
                return [
                    'country' => $geo['country_name'] ?? $geo['country_code'],
                    'city'    => $geo['city'] ?? null,
                ];
            }
        }

        return [
            'country' => 'Unknown',
            'city'    => null,
        ];
    }

    /**
     * Parse User Agent into Browser, OS, and Device.
     */
    protected function parseUserAgent(?string $ua): array
    {
        if (empty($ua)) {
            return ['browser' => 'Unknown', 'os' => 'Unknown', 'device' => 'Desktop'];
        }

        // Browser Detection
        $browser = 'Other';
        if (stripos($ua, 'Edg') !== false) {
            $browser = 'Microsoft Edge';
        } elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Chromium') === false) {
            $browser = 'Google Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            $browser = 'Apple Safari';
        } elseif (stripos($ua, 'PostmanRuntime') !== false) {
            $browser = 'Postman API';
        } elseif (stripos($ua, 'curl') !== false) {
            $browser = 'cURL';
        } elseif (stripos($ua, 'bot') !== false || stripos($ua, 'crawl') !== false || stripos($ua, 'spider') !== false) {
            $browser = 'Bot / Crawler';
        }

        // OS Detection
        $os = 'Other';
        if (stripos($ua, 'Windows NT 10.0') !== false) {
            $os = 'Windows 10/11';
        } elseif (stripos($ua, 'Windows NT 6.3') !== false) {
            $os = 'Windows 8.1';
        } elseif (stripos($ua, 'Windows NT 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        // Device Detection
        $device = 'Desktop';
        if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
            $device = 'Mobile';
        } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
            $device = 'Tablet';
        } elseif (stripos($ua, 'bot') !== false || stripos($ua, 'spider') !== false) {
            $device = 'Bot';
        }

        return [
            'browser' => $browser,
            'os'      => $os,
            'device'  => $device,
        ];
    }
}
