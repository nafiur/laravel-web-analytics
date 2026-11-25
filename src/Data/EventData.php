<?php

namespace Nafiur\WebAnalytics\Data;

final class EventData
{
    /** @param array<string, mixed> $properties */
    public function __construct(
        public readonly int $websiteId,
        public readonly string $name,
        public readonly ?string $visitorHash = null,
        public readonly ?string $sessionKey = null,
        public readonly array $properties = [],
        public readonly ?float $value = null,
        public readonly ?string $occurredAt = null,
        public readonly ?string $requestUuid = null,
        /** Free-form tracking context (path, referrer, user_agent, utm, ...). */
        public readonly array $context = [],
    ) {
    }
}
