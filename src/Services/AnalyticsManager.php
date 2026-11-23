<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\VisitContext;

/**
 * Public API behind the Analytics facade. All methods are no-ops when the
 * advanced analytics schema is disabled.
 */
class AnalyticsManager
{
    public function __construct(
        private Collector $collector,
        private MetricService $metrics,
    ) {
    }

    /** @param array<string, mixed> $options */
    public function pageview(string $path, array $options = []): void
    {
        $websiteId = (int) ($options['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0));
        if ($websiteId <= 0 || !config('laravel-web-analytics.analytics.enabled', false)) {
            return;
        }

        $this->collector->pageview(new VisitContext(
            websiteId:      $websiteId,
            visitorHash:    (string) ($options['visitor_hash'] ?? $this->visitorHash()),
            path:           $path,
            method:         (string) ($options['method'] ?? 'GET'),
            statusCode:     (int) ($options['status_code'] ?? 200),
            responseTimeMs: (float) ($options['response_time_ms'] ?? 0),
            bytesSent:      (int) ($options['bytes_sent'] ?? 0),
            referrer:       $options['referrer'] ?? null,
            utmSource:      $options['utm_source'] ?? null,
            utmMedium:      $options['utm_medium'] ?? null,
            utmCampaign:    $options['utm_campaign'] ?? null,
            title:          $options['title'] ?? null,
            occurredAt:     $options['occurred_at'] ?? now()->toIso8601String(),
        ));
    }

    /** @param array<string, mixed> $properties */
    public function event(string $name, array $properties = [], ?float $value = null, array $options = []): void
    {
        $websiteId = (int) ($options['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0));
        if ($websiteId <= 0 || !config('laravel-web-analytics.analytics.enabled', false)) {
            return;
        }

        $this->collector->event(new EventData(
            websiteId:   $websiteId,
            name:        $name,
            visitorHash: (string) ($options['visitor_hash'] ?? $this->visitorHash()),
            sessionKey:  $options['session_key'] ?? null,
            properties:  $properties,
            value:       $value,
            occurredAt:  $options['occurred_at'] ?? now()->toIso8601String(),
        ));
    }

    public function track(string $name, array $properties = [], ?float $value = null, array $options = []): void
    {
        $this->event($name, $properties, $value, $options);
    }

    public function goal(string $name, array $options = []): void
    {
        $this->event('goal_' . $name, [], null, $options);
    }

    /** @return array<string, mixed> */
    public function summary(string $range = '24h', ?int $websiteId = null, ?string $start = null, ?string $end = null): array
    {
        return $this->metrics->summary($websiteId ?: (int) config('laravel-web-analytics.analytics.website_id', 0), $range, $start, $end);
    }

    /** @return array<string, mixed> */
    public function timeseries(string $range = '24h', ?int $websiteId = null, ?string $start = null, ?string $end = null): array
    {
        return $this->metrics->timeseries($websiteId ?: (int) config('laravel-web-analytics.analytics.website_id', 0), $range, $start, $end);
    }

    private function visitorHash(): string
    {
        $salt = config('laravel-web-analytics.privacy.fingerprint_salt') ?: config('app.key', 'lwa');
        $ip = function_exists('request') ? (string) request()->ip() : '';
        $ua = function_exists('request') ? (string) request()->userAgent() : '';

        return 'f_' . substr(hash('sha256', $ip . '|' . $ua . '|' . now()->toDateString() . '|' . $salt), 0, 32);
    }
}
