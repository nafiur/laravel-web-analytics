<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeoIpService
{
    /**
     * Resolve location information from IP address.
     */
    public static function resolve(string $ip): array
    {
        $default = [
            'country_code' => null,
            'country_name' => null,
            'city'         => null,
        ];

        // Skip private or local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $default;
        }

        $provider = config('laravel-web-analytics.geoip.provider', 'cloudflare');

        if ($provider === 'ip-api') {
            return Cache::remember("lwa_geo_{$ip}", 86400, function () use ($ip, $default) {
                try {
                    $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city");
                    if ($response->successful() && $response->json('status') === 'success') {
                        return [
                            'country_code' => $response->json('countryCode'),
                            'country_name' => $response->json('country'),
                            'city'         => $response->json('city'),
                        ];
                    }
                } catch (\Throwable $e) {
                    // Fail silently to prevent slowing down requests
                }
                return $default;
            });
        }

        return $default;
    }
}
