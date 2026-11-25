<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Data\PathQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;

/**
 * Per-user event paths from lwa_events. One bounded EventStore read feeds both
 * the top-path table and the per-position distribution, so every number has
 * the same source and denominator.
 */
class PathService
{
    private const MAX_STEP_EVENTS = 8;

    /** @return array<string, mixed> */
    public function paths(PathQuery $query): array
    {
        $events = collect(app(EventStore::class)->query(new EventQuery(
            websiteId: $query->websiteId,
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

        /** @var array<string, array<int, AnalyticEvent>> $byUser */
        $byUser = [];
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
            $iso = $date->toIso8601String();
            $cutoff = $cutoff === null || strcmp($iso, $cutoff) > 0 ? $iso : $cutoff;
            $user = $aliases[$hash] ?? $hash;
            $byUser[$user][] = $event;
        }

        /** @var array<string, int> $pathUsers */
        $pathUsers = [];
        /** @var array<int, array<string, int>> $stepUsers */
        $stepUsers = array_fill(0, $query->depth, []);
        $sequenced = 0;
        $singleStep = 0;
        $excluded = 0;

        foreach ($byUser as $userEvents) {
            usort($userEvents, function (AnalyticEvent $a, AnalyticEvent $b): int {
                $left = $a->created_at?->toIso8601String() ?? '';
                $right = $b->created_at?->toIso8601String() ?? '';

                return $left === $right
                    ? ((int) $a->id) <=> ((int) $b->id)
                    : strcmp($left, $right);
            });

            $sequence = [];
            $started = $query->startEvent === null;

            foreach ($userEvents as $event) {
                $name = (string) $event->name;
                if (! $started) {
                    if ($name === $query->startEvent) {
                        $started = true;
                        $sequence[] = $name;
                    }

                    continue;
                }

                $sequence[] = $name;
                if ($query->endEvent !== null && $name === $query->endEvent) {
                    break;
                }
            }

            if (! $started) {
                $excluded++;

                continue;
            }

            // Consecutive repeats are one step: a user refreshing a page does
            // not create a new path variant.
            $steps = [];
            foreach ($sequence as $name) {
                if ($steps === [] || $steps[array_key_last($steps)] !== $name) {
                    $steps[] = $name;
                }
                if (count($steps) >= $query->depth) {
                    break;
                }
            }

            if (count($steps) < 2) {
                $singleStep++;

                continue;
            }

            $sequenced++;
            $key = implode(' → ', $steps);
            $pathUsers[$key] = ($pathUsers[$key] ?? 0) + 1;
            foreach ($steps as $index => $name) {
                $stepUsers[$index][$name] = ($stepUsers[$index][$name] ?? 0) + 1;
            }
        }

        // Sort by users desc, then path key asc so ties are deterministic.
        $keys = array_keys($pathUsers);
        usort($keys, function (string $a, string $b) use ($pathUsers): int {
            return $pathUsers[$a] === $pathUsers[$b]
                ? strcmp($a, $b)
                : $pathUsers[$b] <=> $pathUsers[$a];
        });
        $shown = array_slice($keys, 0, $query->limit);

        $paths = [];
        foreach ($shown as $key) {
            $users = $pathUsers[$key];
            $steps = explode(' → ', $key);
            $paths[] = [
                'steps' => $steps,
                'users' => $users,
                'share' => $sequenced === 0 ? 0.0 : round(($users / $sequenced) * 100, 2),
            ];
        }

        $positions = [];
        foreach ($stepUsers as $index => $counts) {
            uasort($counts, fn (int $a, int $b): int => $b <=> $a);
            $eventsAtPosition = [];
            foreach (array_slice(array_keys($counts), 0, self::MAX_STEP_EVENTS) as $name) {
                $users = $counts[$name];
                $eventsAtPosition[] = [
                    'name'  => (string) $name,
                    'users' => $users,
                    'share' => $sequenced === 0 ? 0.0 : round(($users / $sequenced) * 100, 2),
                ];
            }
            if ($eventsAtPosition !== []) {
                $positions[] = [
                    'position' => $index + 1,
                    'events'   => $eventsAtPosition,
                ];
            }
        }

        return [
            'paths'             => $paths,
            'positions'         => $positions,
            'summary'           => [
                'sequenced_users'   => $sequenced,
                'single_step_users' => $singleStep,
                'excluded_users'    => $excluded,
                'paths_found'       => count($pathUsers),
                'paths_shown'       => count($paths),
                'top_path_share'    => $paths[0]['share'] ?? null,
            ],
            'depth'             => $query->depth,
            'sample_size'       => $sample,
            'capped'            => $capped,
            'cutoff'            => $cutoff,
            'range'             => [
                'from' => $query->from,
                'to'   => $query->to,
            ],
            'definitions'       => $this->definitions($query, $capped, $cutoff),
        ];
    }

    /** @return array<int, string> */
    private function definitions(PathQuery $query, bool $capped, ?string $cutoff): array
    {
        $notes = [
            'Path: up to ' . $query->depth . ' consecutive, distinct event names for one canonical user, ordered by event timestamp then id.',
            'A path starts at the user\'s first matching event in the window' . ($query->startEvent !== null ? ' (' . $query->startEvent . ')' : '') . ' and stops at the first end event' . ($query->endEvent !== null ? ' (' . $query->endEvent . ', included)' : ' or the depth cap') . '.',
            'Consecutive repeats of the same event name collapse into one step. Users without at least two collapsed steps are counted separately as single-step users, not as paths.',
            'Each user contributes exactly one path: their first qualifying sequence. Share = users on that path / sequenced users.',
            'Step distribution counts distinct users with that event at that position; the top ' . self::MAX_STEP_EVENTS . ' events per position are shown.',
            'Users are canonical visitor ids; anonymous ids aliased by identify are folded into the same profile. Events without a visitor id are excluded.',
            'Source: `lwa_events`, scoped to website ' . $query->websiteId . '.',
        ];

        if ($query->propertyKey !== null) {
            $notes[] = 'Property filter: `' . $query->propertyKey . '` = `' . ($query->propertyValue ?? 'any') . '`.';
        }
        if ($capped) {
            $notes[] = 'Sample limit reached: only the ' . $query->maxEvents . ' most recent matching events were read (events before ' . $cutoff . ' are missing); counts are a lower bound and older paths may be incomplete.';
        }

        return $notes;
    }
}
