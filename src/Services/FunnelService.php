<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\FunnelQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;

/**
 * Ordered, per-user funnel engine. One conversion is credited per user: the
 * earliest successful sequence inside the conversion window. All numbers are
 * derived from lwa_events and ship with their definitions.
 */
class FunnelService
{
    private const WINDOW_SECONDS = [
        '1h' => 3600,
        '24h' => 86400,
        '7d' => 604800,
        '30d' => 2592000,
    ];

    /** @return array<string, mixed> */
    public function funnel(FunnelQuery $query): array
    {
        $steps = $query->steps;
        $counts = array_fill(0, count($steps), 0);
        $completed = [];
        $sample = 0;
        $capped = false;

        if (count($steps) < 2) {
            return $this->result($query, [], [], [], 0, null, false, false);
        }

        $events = collect(app(EventStore::class)->query(new EventQuery(
            websiteId: $query->websiteId,
            names: $steps,
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

        $aliases = IdentityAlias::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $query->websiteId)
            ->pluck('distinct_id', 'anonymous_id')->all();

        $users = [];
        foreach ($events as $event) {
            /** @var AnalyticEvent $event */
            $hash = $event->visitor?->visitor_hash;
            if ($hash === null || $hash === '') {
                continue;
            }
            $user = $aliases[$hash] ?? $hash;
            $users[$user][] = $event;
        }

        $window = $query->window === 'all'
            ? PHP_INT_MAX
            : self::WINDOW_SECONDS[$query->window];

        foreach ($users as $userEvents) {
            usort($userEvents, function (AnalyticEvent $a, AnalyticEvent $b): int {
                $left = (int) $a->created_at?->getTimestamp();
                $right = (int) $b->created_at?->getTimestamp();

                return $left === $right ? ((int) $a->id) <=> ((int) $b->id) : $left <=> $right;
            });

            $index = 0;
            $lastAt = null;
            $firstAt = null;
            $progress = 0;
            $duration = null;

            foreach ($userEvents as $event) {
                if ($index >= count($steps)) {
                    break;
                }

                $at = (int) $event->created_at?->getTimestamp();
                $name = (string) $event->name;
                $expired = $index > 0 && $at - (int) $lastAt > $window;

                if ($index > 0 && ! $expired && $name === $steps[$index]) {
                    $index++;
                    $lastAt = $at;
                    $progress = max($progress, $index);
                    if ($index === count($steps)) {
                        $duration = (int) $lastAt - (int) $firstAt;
                        break;
                    }

                    continue;
                }

                if ($expired) {
                    $index = 0;
                    $firstAt = null;
                    $lastAt = null;
                }

                if ($name === $steps[0]) {
                    $index = 1;
                    $firstAt = $at;
                    $lastAt = $at;
                    $progress = max($progress, 1);
                }
            }

            // A user is counted at most once per step, using their furthest
            // progress across attempts within the window.
            for ($i = 0; $i < $progress; $i++) {
                $counts[$i]++;
            }
            if ($duration !== null) {
                $completed[] = $duration;
            }
        }

        sort($completed);
        $median = $completed === []
            ? null
            : $completed[intdiv(count($completed), 2)];

        return $this->result($query, $steps, $counts, $completed, $sample, $median, $capped, true);
    }

    /**
     * @param array<int, string> $steps
     * @param array<int, int> $counts
     * @param array<int, int> $completed
     */
    private function result(
        FunnelQuery $query,
        array $steps,
        array $counts,
        array $completed,
        int $sample,
        ?int $median,
        bool $capped,
        bool $valid,
    ): array {
        $first = $counts[0] ?? 0;
        $rows = [];
        $lastRow = null;
        $previous = null;

        foreach ($steps as $index => $name) {
            $users = $counts[$index] ?? 0;
            $rows[] = [
                'step'                 => $index + 1,
                'event'                => $name,
                'users'                => $users,
                'drop_off'             => $previous === null ? null : max(0, $previous - $users),
                'step_conversion_rate' => $previous === null || $previous === 0
                    ? 0.0
                    : round(($users / $previous) * 100, 2),
                'overall_conversion_rate' => $first === 0
                    ? 0.0
                    : round(($users / $first) * 100, 2),
            ];
            $previous = $users;
            $lastRow = $rows[array_key_last($rows)];
        }

        $definitions = $valid
            ? [
                'Funnel: ordered steps ' . implode(' → ', $steps) . '.',
                'A user reaches a step when that event occurs after the previous step within ' . $query->window . ' of the previous step.',
                'One conversion per user: each user is counted at most once per step, using their furthest progress; later re-entries are ignored.',
                'Users are canonical visitor ids; anonymous ids aliased by identify are folded into the same profile. Events without a visitor id are excluded.',
                'Rates: step conversion = users at this step / users at the previous step; overall conversion = users at this step / users at step 1.',
                'Source: `lwa_events`, scoped to website ' . $query->websiteId . '.',
            ]
            : [
                'At least two non-empty steps are required; no events were read.',
                'Source: `lwa_events`, scoped to website ' . $query->websiteId . '.',
            ];
        if ($valid && $query->propertyKey !== null) {
            $definitions[] = 'Property filter: `' . $query->propertyKey . '` = `' . ($query->propertyValue ?? 'any') . '`.';
        }
        if ($capped) {
            $definitions[] = 'Sample limit reached: only the ' . $query->maxEvents . ' most recent matching events were read; counts are a lower bound.';
        }

        return [
            'steps'                   => $rows,
            'entered_users'           => $first,
            'completed_users'         => $lastRow['users'] ?? 0,
            'overall_conversion_rate' => $lastRow['overall_conversion_rate'] ?? 0.0,
            'median_seconds'          => $median,
            'sample_size'             => $sample,
            'capped'                  => $capped,
            'window'                  => $query->window,
            'definitions'             => $definitions,
        ];
    }
}
