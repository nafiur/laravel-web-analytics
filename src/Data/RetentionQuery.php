<?php

namespace Nafiur\WebAnalytics\Data;

/**
 * Filters for a cohort retention / lifecycle report. Cohorts are the first
 * bucket in which a canonical user has a matching event.
 */
final class RetentionQuery
{
    public const GRANULARITIES = ['day', 'week'];

    public function __construct(
        public readonly int $websiteId = 0,
        public readonly ?string $event = null,
        public readonly string $granularity = 'day',
        public readonly int $periods = 7,
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
        return new self(
            websiteId: (int) ($data['website_id'] ?? 0),
            event: isset($data['event']) && $data['event'] !== '' ? (string) $data['event'] : null,
            granularity: isset($data['granularity']) && in_array($data['granularity'], self::GRANULARITIES, true)
                ? (string) $data['granularity']
                : 'day',
            periods: max(2, min(12, (int) ($data['periods'] ?? 7))),
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
            'event'          => $this->event,
            'granularity'    => $this->granularity,
            'periods'        => $this->periods,
            'from'           => $this->from,
            'to'             => $this->to,
            'property_key'   => $this->propertyKey,
            'property_value' => $this->propertyValue,
            'max_events'     => $this->maxEvents,
        ];
    }
}
