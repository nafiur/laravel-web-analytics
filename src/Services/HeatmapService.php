<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Carbon;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;

/**
 * Aggregates bounded replay markers into click/scroll heatmaps and explicit
 * interaction signals. No JSON functions are used in SQL, so SQLite, MySQL
 * and PostgreSQL produce identical results.
 */
class HeatmapService
{
    private const CAP = 20000;
    private const CLICK_GRID = 2.5;
    private const RAGE_WINDOW_MS = 1000;
    private const DEAD_WINDOW_MS = 2000;

    /** @return array<string, mixed> */
    public function aggregate(?int $websiteId, ?string $tenantId, ?string $path, string $range): array
    {
        $cutoff = $this->cutoff($range);

        $sessionQuery = ReplaySession::query()->forWebsite($websiteId)->forTenant($tenantId);
        if ($path !== null && $path !== '') {
            $sessionQuery->where('url', $path);
        }
        $sessionIds = $sessionQuery->pluck('id')->all();
        if ($sessionIds === []) {
            return $this->emptyResult($range, $path);
        }

        $query = ReplayMarker::query()
            ->whereIn('replay_session_id', $sessionIds)
            ->whereIn('type', ['click', 'scroll']);
        if ($cutoff !== null) {
            $query->where('created_at', '>=', $cutoff);
        }

        $markers = $query->orderByDesc('id')->limit(self::CAP + 1)->get()->reverse()->values();
        $capped = $markers->count() > self::CAP;
        if ($capped) {
            $markers = $markers->slice(-self::CAP)->values();
        }

        $clicks = 0;
        $scrolls = 0;
        $sessions = [];
        $points = [];
        $targets = [];
        $scrollBuckets = array_fill(0, 11, 0);
        $clicksBySessionTarget = [];
        $eventsBySession = [];

        foreach ($markers as $marker) {
            $sessionId = (int) $marker->replay_session_id;
            $sessions[$sessionId] = true;
            $payload = is_array($marker->payload) ? $marker->payload : [];
            $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
            $label = $this->targetLabel(is_array($payload['target'] ?? null) ? $payload['target'] : []);
            $at = (int) $marker->at_ms;

            if ($marker->type === 'click') {
                $clicks++;
                $targets[$label] = ($targets[$label] ?? 0) + 1;
                $clicksBySessionTarget[$sessionId][$label][] = $at;

                $vw = (float) ($data['vw'] ?? 0);
                $vh = (float) ($data['vh'] ?? 0);
                if ($vw > 0 && $vh > 0) {
                    $x = $this->clamp(((float) ($data['x'] ?? 0) / $vw) * 100);
                    $y = $this->clamp(((float) ($data['y'] ?? 0) / $vh) * 100);
                    $bx = round($x / self::CLICK_GRID) * self::CLICK_GRID;
                    $by = round($y / self::CLICK_GRID) * self::CLICK_GRID;
                    $key = $bx . ':' . $by;
                    if (! isset($points[$key])) {
                        $points[$key] = ['x' => $bx, 'y' => $by, 'count' => 0];
                    }
                    $points[$key]['count']++;
                }
            } else {
                $scrolls++;
                $y = (float) ($data['y'] ?? 0);
                $vh = (float) ($data['vh'] ?? 0);
                $dh = (float) ($data['dh'] ?? 0);
                if ($dh > $vh && $vh > 0) {
                    $depth = $this->clamp(($y / ($dh - $vh)) * 100);
                    $bucket = min(10, (int) floor($depth / 10));
                    $scrollBuckets[$bucket]++;
                }
            }

            $eventsBySession[$sessionId][] = [
                'at'    => $at,
                'type'  => $marker->type,
                'label' => $label,
            ];
        }

        $rage = [];
        foreach ($clicksBySessionTarget as $byLabel) {
            foreach ($byLabel as $label => $times) {
                sort($times);
                $count = count($times);
                for ($i = 0; $i < $count - 2; $i++) {
                    if ($times[$i + 2] - $times[$i] <= self::RAGE_WINDOW_MS) {
                        $rage[$label] = ($rage[$label] ?? 0) + 1;
                        break;
                    }
                }
            }
        }

        $dead = [];
        $deadKeys = [];
        foreach ($eventsBySession as $sessionId => $events) {
            usort($events, fn (array $a, array $b): int => $a['at'] <=> $b['at']);
            foreach ($events as $index => $event) {
                if ($event['type'] !== 'click') {
                    continue;
                }
                $followed = false;
                for ($j = $index + 1; $j < count($events); $j++) {
                    $later = $events[$j];
                    if ($later['at'] - $event['at'] > self::DEAD_WINDOW_MS) {
                        break;
                    }
                    if ($later['type'] !== 'click' || $later['label'] !== $event['label']) {
                        $followed = true;
                        break;
                    }
                }
                $key = $sessionId . '|' . $event['label'];
                if (! $followed && ! isset($deadKeys[$key])) {
                    $deadKeys[$key] = true;
                    $dead[$event['label']] = ($dead[$event['label']] ?? 0) + 1;
                }
            }
        }

        $pointList = array_values($points);
        usort($pointList, fn (array $a, array $b): int => $b['count'] <=> $a['count']);
        $targetList = $this->rank($targets);
        $rageList = $this->rank($rage);
        $deadList = $this->rank($dead);

        return [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => $markers->count(),
            'capped'      => $capped,
            'clicks'      => $clicks,
            'scrolls'     => $scrolls,
            'sessions'    => count($sessions),
            'points'      => array_slice($pointList, 0, 300),
            'targets'     => array_slice($targetList, 0, 25),
            'scroll'      => $scrollBuckets,
            'rage'        => array_slice($rageList, 0, 25),
            'dead'        => array_slice($deadList, 0, 25),
            'definitions' => [
                'source'     => 'lwa_replay_markers, scoped to the active website and tenant.',
                'sample'     => 'At most 20,000 most recent matching markers are read; counts are lower bounds when the cap is reached.',
                'click_grid' => 'Clicks are bucketed to a ' . self::CLICK_GRID . '% grid using viewport dimensions captured with the click.',
                'scroll'     => 'Scroll depth is y / (document height - viewport height), bucketed into 10% bands.',
                'rage'       => 'Three or more clicks on the same target within 1,000 ms in one replay session.',
                'dead'       => 'A click with no different marker within 2,000 ms in the same replay session, counted once per session and target.',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function emptyResult(string $range, ?string $path): array
    {
        return [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => 0,
            'capped'      => false,
            'clicks'      => 0,
            'scrolls'     => 0,
            'sessions'    => 0,
            'points'      => [],
            'targets'     => [],
            'scroll'      => array_fill(0, 11, 0),
            'rage'        => [],
            'dead'        => [],
            'definitions' => [
                'source'     => 'lwa_replay_markers, scoped to the active website and tenant.',
                'sample'     => 'At most 20,000 most recent matching markers are read; counts are lower bounds when the cap is reached.',
                'click_grid' => 'Clicks are bucketed to a 2.5% grid using viewport dimensions captured with the click.',
                'scroll'     => 'Scroll depth is y / (document height - viewport height), bucketed into 10% bands.',
                'rage'       => 'Three or more clicks on the same target within 1,000 ms in one replay session.',
                'dead'       => 'A click with no different marker within 2,000 ms in the same replay session, counted once per session and target.',
            ],
        ];
    }

    private function cutoff(string $range): ?Carbon
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? null;

        return $days === null ? null : now()->subDays($days);
    }

    private function clamp(float $value): float
    {
        return max(0, min(100, $value));
    }

    /** @param array<string, mixed> $target */
    private function targetLabel(array $target): string
    {
        $tag = (string) ($target['tag'] ?? 'unknown');
        $id = trim((string) ($target['id'] ?? ''));
        $class = trim((string) ($target['cls'] ?? ''));

        $label = $tag . ($id !== '' ? '#' . $id : '');
        if ($class !== '') {
            $label .= '.' . str_replace(' ', '.', $class);
        }

        return mb_substr($label, 0, 120);
    }

    /** @param array<string, int> $values @return array<int, array<string, mixed>> */
    private function rank(array $values): array
    {
        arsort($values);
        $rows = [];
        foreach ($values as $label => $count) {
            $rows[] = ['label' => (string) $label, 'count' => (int) $count];
        }

        return $rows;
    }
}
