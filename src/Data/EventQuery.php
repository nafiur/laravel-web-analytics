<?php

namespace Nafiur\WebAnalytics\Data;

final class EventQuery
{
    public function __construct(
        public readonly int $websiteId = 0,
        public readonly ?string $name = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        /** Group-by dimensions, e.g. ['country', 'browser']. */
        public readonly ?array $groupBy = null,
        public readonly ?int $limit = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            websiteId: (int) ($data['website_id'] ?? 0),
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            from: isset($data['from']) && is_string($data['from']) ? $data['from'] : null,
            to: isset($data['to']) && is_string($data['to']) ? $data['to'] : null,
            groupBy: (isset($data['group_by']) && is_array($data['group_by'])) ? array_values($data['group_by']) : null,
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'website_id' => $this->websiteId,
            'name' => $this->name,
            'from' => $this->from,
            'to' => $this->to,
            'group_by' => $this->groupBy,
            'limit' => $this->limit,
        ];
    }
}
