<?php

namespace Nafiur\WebAnalytics\Data;

use Carbon\CarbonImmutable;

/**
 * Unified analytics event envelope — v3.2 foundation.
 *
 * Every source (PHP tracker, JS SDK, REST ingest, importer) eventually emits
 * through this shape so the Event Store, dashboards and the Event Explorer can
 * treat all events identically regardless of origin.
 *
 * Identity rules:
 *  - distinct_id  → known user identifier (`u_<id>`), when a real user is known.
 *  - anonymous_id → cookieless fingerprint / session handle (`f_...` / `s_...`).
 */
final class UnifiedEvent
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly CarbonImmutable $timestamp,
        public readonly ?string $distinctId,
        public readonly ?string $anonymousId,
        public readonly ?string $sessionId,
        public readonly int $websiteId,
        public readonly array $properties,
        public readonly array $context,
        public readonly ?float $value,
        public readonly CarbonImmutable $createdAt,
    ) {
    }

    /**
     * @return array<string, mixed> legacy storage payload keys understood by EloquentStorageDriver
     */
    public function toStoragePayload(): array
    {
        return [
            'website_id'   => $this->websiteId,
            'visitor_hash' => $this->distinctId ?? $this->anonymousId ?? 'anonymous',
            'session_key'  => $this->sessionId,
            'name'         => $this->name,
            'properties'   => $this->properties,
            'value'        => $this->value,
            'created_at'   => $this->createdAt->toIso8601String(),
            'request_uuid' => $this->uuid,
        ];
    }
}
