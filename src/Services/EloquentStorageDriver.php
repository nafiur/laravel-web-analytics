<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Nafiur\WebAnalytics\Contracts\StorageDriver;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;

class EloquentStorageDriver implements StorageDriver
{
    public function __construct(private SessionResolver $sessions)
    {
    }

    public function storePageview(array $payload): void
    {
        $websiteId = (int) ($payload['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0));
        if ($websiteId <= 0) {
            return; // no website to attribute the hit to
        }

        $visitorHash = (string) ($payload['visitor_hash'] ?? 'anonymous');
        $occurredAt = isset($payload['created_at'])
            ? Carbon::parse($payload['created_at'])
            : now();

        [$visitor, $session] = $this->sessions->recordPageview(
            $websiteId,
            $visitorHash,
            $occurredAt,
            (string) ($payload['path'] ?? '/'),
            $payload['referrer'] ?? null,
            $payload['utm_source'] ?? null,
            $payload['utm_medium'] ?? null,
            $payload['utm_campaign'] ?? null,
        );

        AnalyticsModels\Pageview::create([
            'website_id'       => $websiteId,
            'session_id'       => $session->id,
            'visitor_id'       => $visitor->id,
            'path'             => (string) ($payload['path'] ?? '/'),
            'title'            => $payload['title'] ?? null,
            'status_code'      => (int) ($payload['status_code'] ?? 200),
            'response_time_ms' => (float) ($payload['response_time_ms'] ?? 0),
            'bytes_sent'       => (int) ($payload['bytes_sent'] ?? 0),
            'created_at'       => $occurredAt,
        ]);
    }

    public function storeEvent(array $payload): void
    {
        $websiteId = (int) ($payload['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0));
        if ($websiteId <= 0) {
            return;
        }

        $occurredAt = isset($payload['created_at'])
            ? Carbon::parse($payload['created_at'])
            : now();

        $sessionId = $payload['session_id'] ?? null;
        $visitorId = $payload['visitor_id'] ?? null;

        if (!empty($payload['visitor_hash'])) {
            [$visitor, $session] = $this->sessions->resolve(
                $websiteId,
                (string) $payload['visitor_hash'],
                $occurredAt
            );
            $visitorId = $visitor->id;
            $sessionId = $session->id;
        }

        AnalyticsModels\AnalyticEvent::create([
            'website_id'  => $websiteId,
            'session_id'  => $sessionId,
            'visitor_id'  => $visitorId,
            'name'        => (string) ($payload['name'] ?? ''),
            'properties'  => $payload['properties'] ?? [],
            'value'       => $payload['value'] ?? null,
            'created_at'  => $occurredAt,
        ]);
    }
}
