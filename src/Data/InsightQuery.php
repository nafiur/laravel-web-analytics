<?php

namespace Nafiur\WebAnalytics\Data;

/**
 * Filters for a single Trends/Insights query. Every query goes through the
 * EventStore so the same definition is used by the UI and the REST API.
 */
final class InsightQuery
{
    public const METRICS = ['events', 'users', 'value'];
    public const GRANULARITIES = ['hour', 'day', 'week', 'month'];

    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly int $websiteId = 0,
        public readonly string $metric = 'events',
        public readonly ?string $name = null,
        public readonly ?string $breakdown = null,
        public readonly string $granularity = 'day',
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?string $propertyKey = null,
        public readonly ?string $propertyValue = null,
        public readonly int $maxEvents = 20000,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $metric = isset($data['metric']) && in_array($data['metric'], self::METRICS, true)
            ? (string) $data['metric']
            : 'events';
        $granularity = isset($data['granularity']) && in_array($data['granularity'], self::GRANULARITIES, true)
            ? (string) $data['granularity']
            : 'day';

        return new self(
            websiteId: (int) ($data['website_id'] ?? 0),
            metric: $metric,
            name: isset($data['name']) && $data['name'] !== '' ? (string) $data['name'] : null,
            breakdown: isset($data['breakdown']) && $data['breakdown'] !== '' ? (string) $data['breakdown'] : null,
            granularity: $granularity,
            from: isset($data['from']) && $data['from'] !== '' ? (string) $data['from'] : null,
            to: isset($data['to']) && $data['to'] !== '' ? (string) $data['to'] : null,
            propertyKey: isset($data['property_key']) && $data['property_key'] !== '' ? (string) $data['property_key'] : null,
            propertyValue: isset($data['property_value']) && $data['property_value'] !== '' ? (string) $data['property_value'] : null,
            maxEvents: max(1, min(50000, (int) ($data['max_events'] ?? 20000))),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'website_id'     => $this->websiteId,
            'metric'         => $this->metric,
            'name'           => $this->name,
            'breakdown'      => $this->breakdown,
            'granularity'    => $this->granularity,
            'from'           => $this->from,
            'to'             => $this->to,
            'property_key'   => $this->propertyKey,
            'property_value' => $this->propertyValue,
            'max_events'     => $this->maxEvents,
        ];
    }
}
