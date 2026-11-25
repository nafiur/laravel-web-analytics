<?php

namespace Nafiur\WebAnalytics\Data;

final class Aggregation
{
    public function __construct(
        public readonly string $metric,
        public readonly ?string $granularity = 'hourly',
        public readonly ?int $websiteId = null,
        public readonly ?string $name = null,
    ) {
    }
}
