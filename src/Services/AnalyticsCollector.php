<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Bus;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Contracts\StorageDriver;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\VisitContext;
use Nafiur\WebAnalytics\Jobs\RecordEvent;
use Nafiur\WebAnalytics\Jobs\RecordPageview;

class AnalyticsCollector implements Collector
{
    /** @var string[] */
    private const BANNED_PROPERTY_KEYS = [
        'password', 'token', 'secret', 'api_key', 'apikey', 'authorization',
        'email', 'phone', 'card', 'card_number', 'cvv', 'ssn', 'address',
    ];

    public function __construct(private StorageDriver $storage)
    {
    }

    public function pageview(VisitContext $context): void
    {
        if (!config('laravel-web-analytics.analytics.enabled', false)) {
            return;
        }

        $payload = [
            'website_id'      => $context->websiteId,
            'visitor_hash'    => $context->visitorHash,
            'path'            => $context->path,
            'title'           => $context->title,
            'method'          => $context->method,
            'status_code'     => $context->statusCode,
            'response_time_ms'=> $context->responseTimeMs,
            'bytes_sent'      => $context->bytesSent,
            'referrer'        => $context->referrer,
            'utm_source'      => $context->utmSource,
            'utm_medium'      => $context->utmMedium,
            'utm_campaign'    => $context->utmCampaign,
            'created_at'      => $context->occurredAt ?? now()->toIso8601String(),
        ];

        $this->dispatch(new RecordPageview($payload));
    }

    public function event(EventData $event): void
    {
        if (!config('laravel-web-analytics.analytics.enabled', false)) {
            return;
        }

        $payload = [
            'website_id'  => $event->websiteId,
            'name'         => substr($event->name, 0, 100),
            'visitor_hash' => $event->visitorHash,
            'session_key'  => $event->sessionKey,
            'properties'  => $this->sanitizeProperties($event->properties),
            'value'       => $event->value,
            'created_at'  => $event->occurredAt ?? now()->toIso8601String(),
        ];

        $this->dispatch(new RecordEvent($payload));
    }

    /** @param array<string, mixed> $properties */
    private function sanitizeProperties(array $properties): array
    {
        $clean = [];
        $count = 0;
        $bytes = 0;

        foreach ($properties as $key => $value) {
            $key = strtolower((string) $key);
            if (in_array($key, self::BANNED_PROPERTY_KEYS, true) || $count >= 20) {
                continue;
            }
            if (!is_scalar($value) && $value !== null) {
                continue;
            }
            $encoded = json_encode([$key => $value]);
            $size = $encoded === false ? 0 : strlen($encoded);
            if ($bytes + $size > 1024) {
                continue;
            }
            $clean[$key] = is_string($value) ? substr($value, 0, 255) : $value;
            $bytes += $size;
            $count++;
        }

        return $clean;
    }

    private function dispatch(object $job): void
    {
        if (config('laravel-web-analytics.analytics.mode', 'sync') === 'queue') {
            Bus::dispatch($job);
            return;
        }

        // Synchronous fallback: run the job handler inline.
        $job->handle($this->storage);
    }
}
