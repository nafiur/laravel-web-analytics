<?php

namespace Nafiur\WebAnalytics\Data;

final class PruneWindow
{
    public function __construct(
        public readonly string $beforeAt,
    ) {
    }
}
