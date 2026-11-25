<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\RetentionQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;

/**
 * Cohort retention and lifecycle from lwa_events only. Cohorts are bucketed in
 * PHP after one bounded EventStore read, so SQLite, MySQL and PostgreSQL agree.
 * Later period cells are emitted only when the whole bucket is inside the
 * selected window; partially observed buckets are null, never zero. Period 0
 * is proven by cohort membership even when the current bucket is still open.
 */
class RetentionService
{
    private const MAX_COHORTS = 12;

    /** @return array<string, mixed> */
    public function retention(RetentionQuery $query): array
    {
        $events = collect(app(EventStore::class)->query(new EventQuery(
            websiteId: $query->websiteId,
            name: $query->event,
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
        $sample = $events->count();

        $tz = (string) config('app.timezone', 'UTC');
        $aliases = IdentityAlias::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $query->websiteId)
            ->pluck('distinct_id', 'anonymous_id')->all();

        /** @var array<string, array<string, true>> $activity */
        $activity = [];
        /** @var array<string, CarbonImmutable> $firstAt */
        $firstAt = [];
        $cutoff = null;

        foreach ($events as $event) {
            /** @var AnalyticEvent $event */
            $hash = $event->visitor?->visitor_hash;
            if ($hash === null || $hash === '') {
                continue;
            }
            $date = $event->created_at;
            if ($date === null) {
                continue;
            }

            $at = CarbonImmutable::parse($date->toIso8601String(), $tz);
            $cutoff = $cutoff === null || $at->greaterThan($cutoff) ? $at : $cutoff;
            $user = $aliases[$hash] ?? $hash;
            $key = $this->key($this->floor($at, $query->granularity), $query->granularity);
            $activity[$user][$key] = true;

            if (! isset($firstAt[$user]) || $at->lessThan($firstAt[$user])) {
                $firstAt[$user] = $at;
            }
        }

        $from = $this->parseBoundary($query->from, $tz);
        $to = $query->to !== null && $query->to !== ''
            ? CarbonImmutable::parse($query->to, $tz)
            : ($cutoff !== null ? $this->ceil($cutoff, $query->granularity) : null);
        $earliest = null;
        foreach ($firstAt as $at) {
            if ($earliest === null || $at->lessThan($earliest)) {
                $earliest = $at;
            }
        }
        $from ??= $earliest !== null ? $this->floor($earliest, $query->granularity) : null;

        if ($from === null || $to === null || $firstAt === []) {
            return $this->emptyResult($query, $sample, $capped, $from, $to, $cutoff);
        }

        $fromFloor = $this->floor($from, $query->granularity);
        $toFloor = $this->floor($to, $query->granularity);

        /** @var array<string, CarbonImmutable> $cohortDates */
        $cohortDates = [];
        /** @var array<string, array<string, true>> $members */
        $members = [];
        foreach ($firstAt as $user => $at) {
            $bucket = $this->floor($at, $query->granularity);
            if ($bucket->lessThan($fromFloor) || $bucket->greaterThan($toFloor)) {
                continue;
            }
            $key = $this->key($bucket, $query->granularity);
            $cohortDates[$key] = $bucket;
            $members[$key][$user] = true;
        }

        uksort($cohortDates, fn (string $a, string $b): int => $cohortDates[$b] <=> $cohortDates[$a]);
        $cohortKeys = array_slice(array_keys($cohortDates), 0, self::MAX_COHORTS);

        $cohorts = [];
        $periodOneUsers = 0;
        $periodOneSize = 0;
        $cohortedUsers = 0;

        foreach ($cohortKeys as $key) {
            $start = $cohortDates[$key];
            $users = $members[$key];
            $size = count($users);
            $cohortedUsers += $size;
            $cells = [];

            for ($period = 0; $period < $query->periods; $period++) {
                $periodStart = $this->step($start, $period, $query->granularity);
                $periodEnd = $this->step($start, $period + 1, $query->granularity);
                // Period 0 is proven by cohort membership even when the
                // current bucket is still open; later periods need a full
                // bucket so a partial day is never reported as retention.
                $observed = $period === 0
                    || (! $periodStart->greaterThan($to) && ! $periodEnd->greaterThan($to));

                if (! $observed) {
                    $cells[] = [
                        'period'   => $period,
                        'label'    => $this->periodLabel($period, $query->granularity),
                        'users'    => null,
                        'rate'     => null,
                        'observed' => false,
                    ];

                    continue;
                }

                $returned = 0;
                foreach (array_keys($users) as $user) {
                    if (isset($activity[$user][$this->key($periodStart, $query->granularity)])) {
                        $returned++;
                    }
                }

                $cells[] = [
                    'period'   => $period,
                    'label'    => $this->periodLabel($period, $query->granularity),
                    'users'    => $returned,
                    'rate'     => $size === 0 ? 0.0 : round(($returned / $size) * 100, 2),
                    'observed' => true,
                ];

                if ($period === 1) {
                    $periodOneUsers += $returned;
                    $periodOneSize += $size;
                }
            }

            $cohorts[] = [
                'key'     => $key,
                'label'   => $this->label($start, $query->granularity),
                'users'   => $size,
                'partial' => $start->lessThan($from),
                'cells'   => $cells,
            ];
        }

        $lifecycle = $this->lifecycle($query, $activity, $firstAt, $fromFloor, $toFloor);

        return [
            'granularity'        => $query->granularity,
            'periods'            => range(0, $query->periods - 1),
            'cohorts'            => $cohorts,
            'lifecycle'          => $lifecycle,
            'summary'            => [
                'cohorts'             => count($cohorts),
                'cohorted_users'      => $cohortedUsers,
                'period_one_users'    => $periodOneUsers,
                'period_one_rate'     => $periodOneSize === 0
                    ? null
                    : round(($periodOneUsers / $periodOneSize) * 100, 2),
                'period_one_cohorts'  => $periodOneSize > 0
                    ? count(array_filter($cohorts, fn (array $cohort): bool => ($cohort['cells'][1]['observed'] ?? false) === true))
                    : 0,
            ],
            'sample_size'        => $sample,
            'capped'             => $capped,
            'cutoff'             => $cutoff?->toIso8601String(),
            'range'              => [
                'from' => $from->toIso8601String(),
                'to'   => $to->toIso8601String(),
            ],
            'definitions'        => $this->definitions($query, $capped, $cutoff),
        ];
    }

    /**
     * @param array<string, array<string, true>> $activity
     * @param array<string, CarbonImmutable> $firstAt
     * @return array<int, array<string, mixed>>
     */
    private function lifecycle(
        RetentionQuery $query,
        array $activity,
        array $firstAt,
        CarbonImmutable $fromFloor,
        CarbonImmutable $toFloor,
    ): array {
        // The bucket containing "now" is still in progress, so lifecycle only
        // reports buckets that have fully elapsed inside the selected window.
        $cursor = $this->step($toFloor, -1, $query->granularity);
        $buckets = [];
        for ($i = 0; $i < $query->periods && ! $cursor->lessThan($fromFloor); $i++) {
            $buckets[] = $cursor;
            $cursor = $this->step($cursor, -1, $query->granularity);
        }
        $buckets = array_reverse($buckets);

        $rows = [];
        foreach ($buckets as $bucket) {
            $key = $this->key($bucket, $query->granularity);
            $previousKey = $this->key($this->step($bucket, -1, $query->granularity), $query->granularity);

            $active = [];
            $previous = [];
            foreach ($activity as $user => $keys) {
                if (isset($keys[$key])) {
                    $active[$user] = true;
                }
                if (isset($keys[$previousKey])) {
                    $previous[$user] = true;
                }
            }

            $new = 0;
            $returning = 0;
            $resurrecting = 0;
            foreach ($active as $user => $_) {
                $firstBucket = $this->key($this->floor($firstAt[$user], $query->granularity), $query->granularity);
                if ($firstBucket === $key) {
                    $new++;
                } elseif (isset($previous[$user])) {
                    $returning++;
                } else {
                    $resurrecting++;
                }
            }

            $dormant = 0;
            foreach ($previous as $user => $_) {
                if (! isset($active[$user])) {
                    $dormant++;
                }
            }

            $rows[] = [
                'key'          => $key,
                'label'        => $this->label($bucket, $query->granularity),
                'new'          => $new,
                'returning'    => $returning,
                'resurrecting' => $resurrecting,
                'dormant'      => $dormant,
            ];
        }

        return $rows;
    }

    /** @return array<string, mixed> */
    private function emptyResult(RetentionQuery $query, int $sample, bool $capped, ?CarbonImmutable $from, ?CarbonImmutable $to, ?CarbonImmutable $cutoff): array
    {
        return [
            'granularity' => $query->granularity,
            'periods'     => range(0, $query->periods - 1),
            'cohorts'     => [],
            'lifecycle'   => [],
            'summary'     => [
                'cohorts'            => 0,
                'cohorted_users'     => 0,
                'period_one_users'   => 0,
                'period_one_rate'    => null,
                'period_one_cohorts' => 0,
            ],
            'sample_size' => $sample,
            'capped'      => $capped,
            'cutoff'      => $cutoff?->toIso8601String(),
            'range'       => [
                'from' => $from?->toIso8601String(),
                'to'   => $to?->toIso8601String(),
            ],
            'definitions' => $this->definitions($query, $capped, $cutoff),
        ];
    }

    /** @return array<int, string> */
    private function definitions(RetentionQuery $query, bool $capped, ?CarbonImmutable $cutoff): array
    {
        $unit = $query->granularity === 'week' ? 'week' : 'day';
        $notes = [
            'Cohort: the ' . $unit . ' of a user\'s first matching event in the selected window; each user belongs to one cohort.',
            'Period N: the Nth full ' . $unit . ' after the cohort ' . $unit . ' (period 0 is the cohort ' . $unit . ' itself). A retained user has at least one matching event in that period.',
            'Rate: retained users in the period / users in the cohort. Period 0 is 100% by definition (cohort membership); later periods require a fully elapsed bucket and are shown as — otherwise, never as zero. A cohort whose first bucket is only partly inside the window is marked partial.',
            'Period 1 retention is weighted by cohort size across fully observed cohorts only.',
            'Lifecycle: New = first sampled activity in this period; Returning = active here and in the previous period; Resurrecting = active here, not in the previous period, with earlier sampled activity; Dormant = active in the previous period but not here.',
            'Lifecycle excludes the in-progress bucket; "first" always means first within the sampled window, not first ever.',
            'Users are canonical visitor ids; anonymous ids aliased by identify are folded into the same profile. Events without a visitor id are excluded.',
            'Source: `lwa_events`, scoped to website ' . $query->websiteId . '. At most ' . self::MAX_COHORTS . ' most recent cohorts are shown.',
        ];

        if ($query->event !== null) {
            $notes[] = 'Event filter: `' . $query->event . '` is required both to enter a cohort and to count as activity in a later period.';
        }
        if ($query->propertyKey !== null) {
            $notes[] = 'Property filter: `' . $query->propertyKey . '` = `' . ($query->propertyValue ?? 'any') . '`.';
        }
        if ($capped) {
            $notes[] = 'Sample limit reached: only the ' . $query->maxEvents . ' most recent matching events were read (events before ' . $cutoff?->toIso8601String() . ' are missing); counts are a lower bound and older cohorts may be incomplete.';
        }

        return $notes;
    }

    private function parseBoundary(?string $value, string $tz): ?CarbonImmutable
    {
        return $value === null || $value === '' ? null : CarbonImmutable::parse($value, $tz);
    }

    private function floor(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return $granularity === 'week'
            ? $date->startOfWeek(CarbonImmutable::MONDAY)
            : $date->startOfDay();
    }

    private function ceil(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return $granularity === 'week'
            ? $date->endOfWeek(CarbonImmutable::SUNDAY)
            : $date->endOfDay();
    }

    private function step(CarbonImmutable $date, int $amount, string $granularity): CarbonImmutable
    {
        return $granularity === 'week' ? $date->addWeeks($amount) : $date->addDays($amount);
    }

    private function key(CarbonImmutable $date, string $granularity): string
    {
        return $granularity === 'week' ? $date->format('o-W') : $date->format('Y-m-d');
    }

    private function label(CarbonImmutable $date, string $granularity): string
    {
        return $granularity === 'week' ? 'Wk ' . $date->format('M j') : $date->format('M j');
    }

    private function periodLabel(int $period, string $granularity): string
    {
        $unit = $granularity === 'week' ? 'W' : 'D';

        return $unit . $period;
    }
}
