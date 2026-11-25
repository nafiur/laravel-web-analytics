<?php

namespace Nafiur\WebAnalytics\Data;

/**
 * Filters for an ordered, per-user funnel. Steps must be event names in the
 * order a user is expected to perform them.
 */
final class FunnelQuery
{
    public const WINDOWS = ['1h', '24h', '7d', '30d', 'all'];

    /** @param array<int, string> $steps */
    public function __construct(
        public readonly int $websiteId = 0,
        public readonly array $steps = [],
        public readonly string $window = '7d',
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
        $steps = isset($data['steps']) && is_array($data['steps'])
            ? array_values(array_filter($data['steps'], fn ($value): bool => is_string($value) && $value !== ''))
            : [];

        return new self(
            websiteId: (int) ($data['website_id'] ?? 0),
            steps: array_slice($steps, 0, 10),
            window: isset($data['window']) && in_array($data['window'], self::WINDOWS, true)
                ? (string) $data['window']
                : '7d',
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
            'steps'          => $this->steps,
            'window'         => $this->window,
            'from'           => $this->from,
            'to'             => $this->to,
            'property_key'   => $this->propertyKey,
            'property_value' => $this->propertyValue,
            'max_events'     => $this->maxEvents,
        ];
    }
}
