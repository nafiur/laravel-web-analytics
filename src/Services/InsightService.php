<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\InsightQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;

/**
 * Shared Trends engine for the UI and REST API. Bucketing is done in PHP from
 * a bounded EventStore read so date truncation behaves identically on SQLite,
 * MySQL and PostgreSQL. Every returned number has a documented definition.
 */
class InsightService
{
    private const MAX_BUCKETS = 1000;
    private const MAX_SERIES = 6;

    /** @return array<string, mixed> */
    public function trend(InsightQuery $query): array
    {
        $events = collect(app(EventStore::class)->query(new EventQuery(
            websiteId: $query->websiteId,
            name: $query->name,
            from: $query->from,
            to: $query->to,
            limit: $query->maxEvents + 1,
            propertyKey: $query->propertyKey,
            propertyValue: $query->propertyValue,
        )));

        $capped = $events->count() > $query->maxEvents;
        if ($capped) {
            $events = $events->take($query->maxEvents);
        }

        $tz = (string) config('app.timezone', 'UTC');
        $from = $this->boundary($query->from, $tz, true);
        $to = $this->boundary($query->to, $tz, false);

        if ($events->isNotEmpty()) {
            $first = $events->min(fn (AnalyticEvent $event): string => (string) $event->created_at?->toIso8601String());
            $last = $events->max(fn (AnalyticEvent $event): string => (string) $event->created_at?->toIso8601String());
            $from ??= $first === '' ? null : CarbonImmutable::parse($first, $tz);
            $to ??= $last === '' ? null : CarbonImmutable::parse($last, $tz);
        }

        $granularity = $this->effectiveGranularity($query, $from, $to);
        $aliases = $query->metric === 'users'
            ? IdentityAlias::withoutGlobalScope(WebsiteScope::class)
                ->where('website_id', $query->websiteId)
                ->pluck('distinct_id', 'anonymous_id')->all()
            : [];

        $buckets = $this->buckets($from, $to, $granularity);
        $series = [];
        $sets = [];
        $totals = [];
        $globalUsers = [];

        foreach ($events as $event) {
            /** @var AnalyticEvent $event */
            $bucket = $this->bucketKey($event, $tz, $granularity);
            if ($bucket === null || ! array_key_exists($bucket, $buckets)) {
                continue;
            }

            $group = $query->breakdown !== null
                ? $this->breakdownValue($event, $query->breakdown)
                : $this->metricLabel($query->metric);
            $series[$group] ??= array_fill_keys(array_keys($buckets), 0);
            if ($query->metric === 'users') {
                $sets[$group] ??= array_fill_keys(array_keys($buckets), []);
            }

            if ($query->metric === 'events') {
                $series[$group][$bucket]++;
                $totals[$group] = ($totals[$group] ?? 0) + 1;
            } elseif ($query->metric === 'value') {
                $value = (float) ($event->value ?? 0);
                $series[$group][$bucket] += $value;
                $totals[$group] = ($totals[$group] ?? 0) + $value;
            } else {
                $hash = $event->visitor?->visitor_hash;
                if ($hash === null || $hash === '') {
                    continue;
                }
                $canonical = $aliases[$hash] ?? $hash;
                $sets[$group][$bucket][$canonical] = true;
                $globalUsers[$canonical] = true;
            }
        }

        if ($query->metric === 'users') {
            foreach ($sets as $group => $perBucket) {
                $series[$group] = array_map(fn (array $users): int => count($users), $perBucket);
                $setsByGroup = array_values($perBucket);
                $totals[$group] = $setsByGroup === []
                    ? 0
                    : count(array_unique(array_merge(...$setsByGroup)));
            }
        }

        arsort($totals);
        $groups = array_slice(array_keys($totals), 0, self::MAX_SERIES, true);

        return [
            'labels'               => array_values($buckets),
            'series'               => array_map(
                fn (string $group): array => [
                    'name' => $group,
                    'data' => array_values($series[$group]),
                ],
                $groups,
            ),
            'totals'               => array_map(
                fn (string $group): array => [
                    'name'  => $group,
                    'value' => $query->metric === 'value'
                        ? round((float) $totals[$group], 2)
                        : (int) $totals[$group],
                ],
                $groups,
            ),
            'global_total'         => match ($query->metric) {
                'users' => count($globalUsers),
                'value' => round(array_sum(array_map(fn (string $group): float => (float) $totals[$group], $groups)), 2),
                default => (int) array_sum(array_map(fn (string $group): int => (int) $totals[$group], $groups)),
            },
            'granularity'          => $granularity,
            'granularity_requested'=> $query->granularity,
            'sample_size'          => $events->count(),
            'capped'               => $capped,
            'range'                => [
                'from' => $from?->toIso8601String(),
                'to'   => $to?->toIso8601String(),
            ],
            'definitions'          => $this->definitions($query, $granularity, $capped),
        ];
    }

    private function definitions(InsightQuery $query, string $granularity, bool $capped): array
    {
        $metric = match ($query->metric) {
            'users' => 'Distinct visitor ids with a matching event in each bucket. Anonymous ids aliased to a profile are counted once under that profile; events without a visitor id are excluded.',
            'value' => 'Sum of the numeric `value` field on matching `lwa_events` rows in each bucket.',
            default => 'Count of matching `lwa_events` rows in each bucket.',
        };

        $notes = [
            'Metric: ' . $metric,
            'Bucket: ' . $granularity . ' in the application timezone (' . config('app.timezone', 'UTC') . '). Empty buckets are zero.',
            'Source: `lwa_events`, scoped to website ' . $query->websiteId . '.',
        ];

        if ($query->breakdown !== null) {
            $notes[] = 'Breakdown: property `' . $query->breakdown . '`; missing values appear as `(not set)`. Only the top ' . self::MAX_SERIES . ' groups by total are charted. For the users metric, a visitor can appear in more than one group, so the chart total can exceed the distinct global total.';
        }
        if ($query->name !== null) {
            $notes[] = 'Event filter: `' . $query->name . '`.';
        }
        if ($query->propertyKey !== null) {
            $notes[] = 'Property filter: `' . $query->propertyKey . '` = `' . ($query->propertyValue ?? 'any') . '`.';
        }
        if ($capped) {
            $notes[] = 'Sample limit reached: only the first ' . $query->maxEvents . ' matching events were read; totals are a lower bound.';
        }

        return $notes;
    }

    private function metricLabel(string $metric): string
    {
        return match ($metric) {
            'users' => 'Unique users',
            'value' => 'Value',
            default => 'Events',
        };
    }

    private function boundary(?string $value, string $tz, bool $start): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = CarbonImmutable::parse($value, $tz);

        return $start ? $date->startOfDay() : $date->endOfDay();
    }

    private function effectiveGranularity(InsightQuery $query, ?CarbonImmutable $from, ?CarbonImmutable $to): string
    {
        $granularity = $query->granularity;
        $days = $from !== null && $to !== null ? $from->diffInDays($to) : 0;

        if ($granularity === 'hour' && $days > 14) {
            $granularity = 'day';
        }
        if ($granularity === 'day' && $days > 400) {
            $granularity = 'week';
        }
        if ($granularity === 'week' && $days > 1000) {
            $granularity = 'month';
        }

        if ($from !== null && $to !== null) {
            $count = 0;
            for ($cursor = $this->floor($from, $granularity); $cursor->lessThanOrEqualTo($to) && $count <= self::MAX_BUCKETS; $cursor = $this->step($cursor, $granularity)) {
                $count++;
            }
            if ($count > self::MAX_BUCKETS && $granularity !== 'month') {
                $granularity = match ($granularity) {
                    'hour' => 'day',
                    'day' => 'week',
                    default => 'month',
                };
            }
        }

        return $granularity;
    }

    /** @return array<string, string> */
    private function buckets(?CarbonImmutable $from, ?CarbonImmutable $to, string $granularity): array
    {
        if ($from === null || $to === null) {
            return [];
        }

        $out = [];
        $cursor = $this->floor($from, $granularity);
        for ($i = 0; $cursor->lessThanOrEqualTo($to) && $i < self::MAX_BUCKETS; $i++, $cursor = $this->step($cursor, $granularity)) {
            $out[$this->key($cursor, $granularity)] = $this->label($cursor, $granularity);
        }

        return $out;
    }

    private function bucketKey(AnalyticEvent $event, string $tz, string $granularity): ?string
    {
        $date = $event->created_at;
        if ($date === null) {
            return null;
        }

        return $this->key(CarbonImmutable::parse($date->toIso8601String(), $tz), $granularity);
    }

    private function floor(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'hour' => $date->startOfHour(),
            'week' => $date->startOfWeek(),
            'month' => $date->startOfMonth(),
            default => $date->startOfDay(),
        };
    }

    private function step(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'hour' => $date->addHour(),
            'week' => $date->addWeek(),
            'month' => $date->addMonth(),
            default => $date->addDay(),
        };
    }

    private function key(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('Y-m-d-H'),
            'week' => $date->format('o-W'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function label(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('M j, H:00'),
            'week' => 'Wk ' . $date->format('M j'),
            'month' => $date->format('M Y'),
            default => $date->format('M j'),
        };
    }

    private function breakdownValue(AnalyticEvent $event, string $property): string
    {
        $properties = $event->properties ?? [];
        if (! array_key_exists($property, $properties)) {
            return '(not set)';
        }

        $value = $properties[$property];

        return is_scalar($value)
            ? (string) $value
            : (json_encode($value) ?: '(not set)');
    }
}
