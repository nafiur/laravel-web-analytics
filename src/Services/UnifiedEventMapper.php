<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\UnifiedEvent;

/**
 * Maps tracker events (PHP facade, JS SDK, REST, importer) onto the unified
 * event envelope used by the v3.2 Event Store.
 */
class UnifiedEventMapper
{
    private const MAX_NAME_LENGTH = 100;
    private const MAX_PROPERTIES = 20;
    private const MAX_PROPERTY_BYTES = 1024;
    private const MAX_PROPERTY_STRING_LENGTH = 255;

    /** @var list<string> */
    private const BANNED_PROPERTY_KEYS = [
        'password', 'token', 'secret', 'api_key', 'apikey', 'authorization',
        'email', 'phone', 'card', 'card_number', 'cvv', 'ssn', 'address',
    ];

    private const DEFAULT_CONTEXT = [
        'path'         => '',
        'method'       => 'GET',
        'status_code' => null,
        'referrer'    => null,
        'user_agent'  => '',
        'ip_address'  => '',
        'utm_source'  => null,
        'utm_medium'  => null,
        'utm_campaign' => null,
    ];

    public function map(EventData $event, array $context = []): UnifiedEvent
    {
        $timestamp = $event->occurredAt !== null && $event->occurredAt !== ''
            ? CarbonImmutable::parse($event->occurredAt)
            : CarbonImmutable::now();

        $visitorHash = (string) ($event->visitorHash ?? '');
        $isKnownUser = str_starts_with($visitorHash, 'u_');

        return new UnifiedEvent(
            uuid:         $event->requestUuid ?: (string) Str::uuid(),
            name:         substr($event->name, 0, self::MAX_NAME_LENGTH),
            timestamp:    $timestamp,
            distinctId:  $isKnownUser ? $visitorHash : null,
            anonymousId: (! $isKnownUser && $visitorHash !== '') ? $visitorHash : null,
            sessionId:    $event->sessionKey,
            websiteId:   $event->websiteId,
            properties:  self::sanitizeProperties($event->properties),
            context:      array_merge(self::DEFAULT_CONTEXT, $event->context, $context),
            value:        $event->value,
            createdAt:    $timestamp,
        );
    }

    /** @param array<string, mixed> $properties */
    private function sanitizeProperties(array $properties): array
    {
        $clean = [];
        $count = 0;
        $bytes = 0;

        foreach ($properties as $key => $value) {
            $key = strtolower((string) $key);
            if (in_array($key, self::BANNED_PROPERTY_KEYS, true) || $count >= self::MAX_PROPERTIES) {
                continue;
            }
            if (! is_scalar($value) && $value !== null) {
                continue;
            }
            $encoded = json_encode([$key => $value]);
            $size = $encoded === false ? 0 : strlen($encoded);
            if ($bytes + $size > self::MAX_PROPERTY_BYTES) {
                continue;
            }
            $clean[$key] = is_string($value) ? substr($value, 0, self::MAX_PROPERTY_STRING_LENGTH) : $value;
            $bytes += $size;
            $count++;
        }

        return $clean;
    }
}
