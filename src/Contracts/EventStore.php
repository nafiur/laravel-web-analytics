<?php

namespace Nafiur\WebAnalytics\Contracts;

use Nafiur\WebAnalytics\Data\Aggregation;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\PruneWindow;

interface EventStore
{
    /**
     * Persist a batch of unified events.
     *
     * @param array<int, UnifiedEvent|array<string, mixed>> $events
     */
    public function insert(array $events): void;

    public function query(EventQuery $query): iterable;

    public function aggregate(Aggregation $aggregation): array;

    public function prune(PruneWindow $window): int;
}
