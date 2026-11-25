<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Contracts\StorageDriver;
use Nafiur\WebAnalytics\Data\Aggregation;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\PruneWindow;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;
use Nafiur\WebAnalytics\Services\IdentityService;
use Nafiur\WebAnalytics\Services\WebsiteScope;

/**
 * Relational EventStore implementation used by the default (self-hosted) setup.
 * Writes go through the existing collector storage driver so pageviews and
 * custom events share one persistence path; ClickHouse remains an optional
 * later adapter.
 */
class EloquentEventStore implements EventStore
{
    public function __construct(
        private StorageDriver $storage,
        private IdentityService $identity,
    ) {
    }

    public function insert(array $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof UnifiedEvent
                && $event->name === 'identify'
                && $event->distinctId !== null
                && $event->anonymousId !== null) {
                $this->identity->alias(
                    $event->websiteId,
                    $event->anonymousId,
                    $event->distinctId,
                    $event->properties,
                );
            }

            $payload = $event instanceof UnifiedEvent ? $event->toStoragePayload() : (array) $event;
            $this->storage->storeEvent($payload);
        }
    }

    public function query(EventQuery $query): iterable
    {
        $builder = AnalyticEvent::query()
            ->where('website_id', $query->websiteId);

        $names = array_values(array_filter($query->names ?? [], fn ($value): bool => is_string($value) && $value !== ''));
        if ($names !== []) {
            $builder->whereIn('name', $names);
        } elseif ($query->name !== null && $query->name !== '') {
            $builder->where('name', $query->name);
        }

        if ($query->from !== null && $query->from !== '') {
            $builder->where('created_at', '>=', $query->from);
        }
        if ($query->to !== null && $query->to !== '') {
            $builder->where('created_at', '<=', $query->to);
        }

        if ($query->visitorHash !== null && $query->visitorHash !== '') {
            $visitorIds = Visitor::withoutGlobalScope(WebsiteScope::class)
                ->where('website_id', $query->websiteId)
                ->where('visitor_hash', $query->visitorHash)
                ->pluck('id');

            $builder->whereIn('visitor_id', $visitorIds);
        }

        // Property predicates are evaluated after hydration so they work
        // identically on SQLite / MySQL / PostgreSQL (JSON casting differs).
        if ($query->propertyKey === null || $query->propertyKey === '') {
            if ($query->limit !== null) {
                $builder->limit($query->limit);
            }

            return $builder->with('visitor')->orderByDesc('created_at')->get();
        }

        $events = $builder->with('visitor')->orderByDesc('created_at')->get()->filter(function (AnalyticEvent $event) use ($query): bool {
            $properties = $event->properties ?? [];
            if (! array_key_exists($query->propertyKey, $properties)) {
                return false;
            }

            return $query->propertyValue === null
                || (string) $properties[$query->propertyKey] === $query->propertyValue;
        });

        return $query->limit !== null ? $events->take($query->limit)->values() : $events->values();
    }

    public function aggregate(Aggregation $aggregation): array
    {
        $query = AnalyticEvent::query()->where('website_id', (int) ($aggregation->websiteId ?? 0));

        if ($aggregation->name !== null && $aggregation->name !== '') {
            $query->where('name', $aggregation->name);
        }

        $events = $query->get();
        $valueSum = 0.0;
        foreach ($events as $event) {
            $valueSum += (float) ($event->value ?? 0);
        }

        return $aggregation->metric === 'sum_value'
            ? ['sum_value' => $valueSum]
            : ['count' => $events->count()];
    }

    public function prune(PruneWindow $window): int
    {
        return (int) AnalyticEvent::query()
            ->where('created_at', '<', $window->beforeAt)
            ->delete();
    }
}
