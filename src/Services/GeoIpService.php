<?php
namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIpService
{
    /**
     * Resolve location information from IP address.
     *
     * @return array{country_code: ?string, country_name: ?string, city: ?string, latitude: ?float, longitude: ?float}
     */
    public static function resolve(string $ip): array
    {
        $default = [
            'country_code' => null,
            'country_name' => null,
            'city'         => null,
            'latitude'     => null,
            'longitude'    => null,
        ];

        // Skip private or local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $default;
        }

        $provider = config('laravel-web-analytics.geoip.provider', '');

        if ($provider === 'maxmind') {
            return self::resolveMaxMind($ip, $default);
        }

        if ($provider === 'ip-api') {
            return Cache::remember("lwa_geo_{$ip}", 86400, function () use ($ip, $default) {
                try {
                    $response = Http::timeout(max(1, (int) config('laravel-web-analytics.geoip.timeout', 1)))
                        ->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city,lat,lon");
                    if ($response->successful() && $response->json('status') === 'success') {
                        return [
                            'country_code' => $response->json('countryCode'),
                            'country_name' => $response->json('country'),
                            'city'         => $response->json('city'),
                            'latitude'     => $response->json('lat'),
                            'longitude'    => $response->json('lon'),
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

    /** @param array<string, mixed> $default */
    private static function resolveMaxMind(string $ip, array $default): array
    {
        return Cache::remember("lwa_geo_maxmind_{$ip}", 86400, function () use ($ip, $default) {
            $path = config('laravel-web-analytics.geoip.maxmind_db_path');

            if (!$path || !is_file($path) || !class_exists(\MaxMind\Db\Reader::class)) {
                return $default; // optional dependency / database not installed
            }

            $reader = null;
            try {
                $reader = new \MaxMind\Db\Reader($path);
                $record = $reader->get($ip);
                if (!is_array($record)) {
                    return $default;
                }

                return [
                    'country_code' => $record['country']['iso_code'] ?? null,
                    'country_name' => $record['country']['names']['en'] ?? null,
                    'city'         => $record['city']['names']['en'] ?? null,
                    'latitude'     => $record['location']['latitude'] ?? null,
                    'longitude'    => $record['location']['longitude'] ?? null,
                ];
            } catch (\Throwable $e) {
                return $default;
            } finally {
                if ($reader !== null) {
                    try {
                        $reader->close();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        });
    }
}
