<?php

namespace Nafiur\WebAnalytics\Data;

/**
 * Filters for per-user event paths. A path is the first bounded sequence of
 * consecutive, distinct event names for one canonical user.
 */
final class PathQuery
{
    public function __construct(
        public readonly int $websiteId = 0,
        public readonly ?string $startEvent = null,
        public readonly ?string $endEvent = null,
        public readonly int $depth = 5,
        public readonly int $limit = 20,
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
            startEvent: isset($data['start_event']) && $data['start_event'] !== '' ? (string) $data['start_event'] : null,
            endEvent: isset($data['end_event']) && $data['end_event'] !== '' ? (string) $data['end_event'] : null,
            depth: max(2, min(8, (int) ($data['depth'] ?? 5))),
            limit: max(5, min(50, (int) ($data['limit'] ?? 20))),
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
            'start_event'    => $this->startEvent,
            'end_event'      => $this->endEvent,
            'depth'          => $this->depth,
            'limit'          => $this->limit,
            'from'           => $this->from,
            'to'             => $this->to,
            'property_key'   => $this->propertyKey,
            'property_value' => $this->propertyValue,
            'max_events'     => $this->maxEvents,
        ];
    }
}
