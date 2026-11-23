<?php
namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Data\VisitContext;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\AlertService;
use Nafiur\WebAnalytics\Services\BotDetector;
use Nafiur\WebAnalytics\Services\GeoIpService;
use Nafiur\WebAnalytics\Services\IpAddress;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\UrlMasker;
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
        if (! config('laravel-web-analytics.enabled', true)) {
            return;
        }

        try {
            // Respect Do Not Track before doing any other work.
            if (config('laravel-web-analytics.privacy.respect_dnt', true)
                && ($request->header('DNT') === '1' || $request->server('HTTP_DNT') === '1')) {
                return;
            }

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
            $uaInfo    = $this->parseUserAgent($userAgent);

            // Bot / crawler filtering (false | true | 'known')
            if (! BotDetector::shouldCapture($userAgent)) {
                return;
            }

            // Check capture API / AJAX settings
            if (($request->is('api/*') || $request->expectsJson()) && ! config('laravel-web-analytics.capture.api', true)) {
                return;
            }
            if ($request->ajax() && ! config('laravel-web-analytics.capture.ajax', true)) {
                return;
            }
            if (! $request->is('api/*') && ! $request->expectsJson() && ! $request->ajax()
                && ! config('laravel-web-analytics.capture.web', true)) {
                return;
            }

            $startTime  = $request->attributes->get('_lwa_start_time', defined('LARAVEL_START') ? constant('LARAVEL_START') : microtime(true));
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            // Response byte size calculation
            $bytes = 0;
            if ($response->headers->has('Content-Length')) {
                $bytes = (int) $response->headers->get('Content-Length');
            } else {
                $content = $response->getContent();
                $bytes   = is_string($content) ? strlen($content) : 0;
            }

            // Resolve location (Country & City)
            $location = $this->resolveLocation($request, $ip);

            // Capture authenticated user ID without depending on any User model
            $userId = null;
            try {
                $userId = $request->user()?->getAuthIdentifier() ?? (function_exists('auth') ? auth()->id() ?? auth('web')->id() : null);
            } catch (\Throwable $e) {
                // Ignore auth inspection errors
            }

            // Visitor identifier:
            //   authenticated -> 'u_' . user id
            //   fingerprint   -> 'f_' . daily salted hash (cookieless)
            //   session       -> 's_' . Laravel session id, with stateless fallback
            $sessionId = null;
            $strategy  = config('laravel-web-analytics.privacy.visitor_id_strategy', 'session');

            if ($userId) {
                $sessionId = 'u_' . $userId;
            } elseif ($strategy === 'fingerprint') {
                $salt      = config('laravel-web-analytics.privacy.fingerprint_salt') ?: config('app.key', 'lwa');
                $sessionId = 'f_' . substr(hash('sha256', $ip . '|' . $userAgent . '|' . now()->toDateString() . '|' . $salt), 0, 32);
            } else {
                try {
                    if ($request->hasSession()) {
                        $laravelSessionId = $request->session()->getId();
                        if ($laravelSessionId) {
                            $sessionId = 's_' . substr($laravelSessionId, 0, 40);
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore session retrieval issues
                }
                if (! $sessionId) {
                    $sessionId = 'f_' . substr(md5($ip . '|' . $userAgent), 0, 24);
                }
            }

            // Referrer
            $rawReferrer = $request->headers->get('referer');
            $maskedFullUrl = UrlMasker::mask($request->fullUrl());
            $referrer = $rawReferrer
                ? substr((string) (config('laravel-web-analytics.privacy.mask_referrer', true)
                    ? UrlMasker::mask($rawReferrer)
                    : $rawReferrer), 0, 490)
                : 'Direct';

            // UTM campaign parameters
            $utmSource   = $request->query('utm_source');
            $utmMedium   = $request->query('utm_medium');
            $utmCampaign = $request->query('utm_campaign');

            // Alerts (disabled by default). Counters and dedupe keep webhook
            // traffic bounded even during an outage.
            if (config('laravel-web-analytics.alerts.enabled', false)) {
                AlertService::recordRequest($response->getStatusCode() >= 500);
                AlertService::maybeNotifySpike($request->fullUrl(), $ip);
                if ($response->getStatusCode() >= 500) {
                    AlertService::notifyErrorSpike($response->getStatusCode(), $request->fullUrl(), $ip);
                }
            }

            // Resolve tenant identifier if multi-tenancy is enabled
            $tenantId = TenantResolver::resolve($request);

            if (config('laravel-web-analytics.analytics.write_legacy', true)) {
                LwaLogs::create([
                    'tenant_id'        => $tenantId ? substr($tenantId, 0, 100) : null,
                    'session_id'       => substr($sessionId, 0, 100),
                    'ip_address'       => substr($ip, 0, 45),
                    'method'           => strtoupper($request->method()),
                    'path'             => substr($path, 0, 490),
                    'full_url'         => substr((string) $maskedFullUrl, 0, 1000),
                    'status_code'      => $response->getStatusCode(),
                    'response_time_ms' => $durationMs,
                    'bytes_sent'       => $bytes,
                    'country'          => $location['country'],
                    'city'             => $location['city'],
                    'browser'          => $uaInfo['browser'],
                    'os'               => $uaInfo['os'],
                    'device'           => $uaInfo['device'],
                    'referrer'         => $referrer,
                    'utm_source'       => $utmSource !== null ? substr((string) $utmSource, 0, 100) : null,
                    'utm_medium'       => $utmMedium !== null ? substr((string) $utmMedium, 0, 100) : null,
                    'utm_campaign'     => $utmCampaign !== null ? substr((string) $utmCampaign, 0, 100) : null,
                    'user_agent'       => substr($userAgent, 0, 500),
                    'user_id'          => $userId,
                ]);
            }

            // Advanced analytics (opt-in): feed the v3-style collector.
            $advancedWebsiteId = config('laravel-web-analytics.analytics.website_id');
            if (config('laravel-web-analytics.analytics.enabled', false) && $advancedWebsiteId) {
                app(Collector::class)->pageview(new VisitContext(
                    websiteId:      (int) $advancedWebsiteId,
                    visitorHash:    $sessionId,
                    path:           $path,
                    method:         strtoupper($request->method()),
                    statusCode:     $response->getStatusCode(),
                    responseTimeMs: $durationMs,
                    bytesSent:      $bytes,
                    referrer:       $referrer,
                    utmSource:      $utmSource !== null ? (string) $utmSource : null,
                    utmMedium:      $utmMedium !== null ? (string) $utmMedium : null,
                    utmCampaign:    $utmCampaign !== null ? (string) $utmCampaign : null,
                    occurredAt:     now()->toIso8601String(),
                ));
            }
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
        $ext     = strtolower(pathinfo($path, PATHINFO_EXTENSION));

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
        // Forwarded headers are only honored when explicitly trusted. Laravel's
        // Request::ip() already respects the application's trusted-proxy config.
        if (config('laravel-web-analytics.capture.trust_forwarded_headers', false)) {
            $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP'];
            foreach ($headers as $header) {
                $candidate = $request->server($header);
                if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }

            if ($forwarded = $request->server('HTTP_X_FORWARDED_FOR')) {
                $parts     = explode(',', $forwarded);
                $candidate = trim($parts[0]);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        $ip = $request->ip();
        return ($ip && filter_var($ip, FILTER_VALIDATE_IP)) ? $ip : '127.0.0.1';
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
        // 1.  headers
        if ($cfCountry = $request->server('HTTP_CF_IPCOUNTRY')) {
            return [
                'country' => strtoupper($cfCountry),
                'city'    => $request->server('HTTP_CF_IPCITY'),
            ];
        }

        // 2. Localhost / Private IPs
        if (IpAddress::isPrivate($ip) || strtolower($ip) === 'localhost') {
            return [
                'country' => 'Localhost / Internal',
                'city'    => 'Local',
            ];
        }

        // 3. GeoIP lookup service (if enabled)
        if (config('laravel-web-analytics.geoip.enabled', true)) {
            $geo = GeoIpService::resolve($ip);
            if (! empty($geo['country_name']) || ! empty($geo['country_code'])) {
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
