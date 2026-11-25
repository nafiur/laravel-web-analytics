<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Contracts\StorageDriver;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\VisitContext;
use Nafiur\WebAnalytics\Jobs\RecordEvent;
use Nafiur\WebAnalytics\Jobs\RecordPageview;

class AnalyticsCollector implements Collector
{
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
            'request_uuid'    => $context->requestUuid ?: (string) Str::uuid(),
        ];

        $this->dispatch(new RecordPageview($payload));
    }

    public function event(EventData $event): void
    {
        if (!config('laravel-web-analytics.analytics.enabled', false)) {
            return;
        }

        // v3.2: events flow through the unified envelope before hitting storage.
        $unified = app(UnifiedEventMapper::class)->map($event, $event->context);
        $payload = $unified->toStoragePayload();

        $this->dispatch(new RecordEvent($payload));
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
