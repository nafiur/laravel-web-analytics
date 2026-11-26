<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Models\LwaLogs;

/**
 * Per-path reports from bounded lwa_logs rows. Every count, rate and path
 * relationship is derived from recorded requests; nothing is inferred.
 */
class PageReportsService
{
    private const CAP = 20000;

    public function __construct(private ComparisonService $comparison)
    {
    }

    /** @return array<string, mixed> */
    public function report(?int $websiteId, ?string $tenantId, string $range, ?string $path, bool $compare): array
    {
        [$from, $to, $range] = AnalyticsWindow::resolve([
            'range'      => $range,
            'start_date' => null,
            'end_date'   => null,
        ]);
        $tenantId = $tenantId !== '' ? $tenantId : null;

        $rows = $this->fetch($tenantId, $from, $to);
        $current = $this->aggregate($rows, $path);
        $paths = $this->pathRows($rows);

        $result = [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => count($rows),
            'capped'      => count($rows) > self::CAP,
            'summary'     => $current,
            'paths'       => $paths,
            'comparison'  => null,
            'definitions' => $this->definitions(),
        ];

        if ($compare && $range !== 'all') {
            [$previousFrom, $previousTo] = AnalyticsWindow::previous([
                'range' => $range,
            ]);
            if ($previousFrom !== null && $previousTo !== null) {
                $previousRows = $this->fetch($tenantId, $previousFrom, $previousTo);
                $previous = $this->aggregate($previousRows, $path);
                $result['comparison'] = $this->comparison->deltas($this->comparisonSummary($current), $this->comparisonSummary($previous));
            }
        }

        return $result;
    }

    /**
     * @return array<int, array{path: string, session: string, at: int}>
     */
    private function fetch(?string $tenantId, ?string $from, ?string $to): array
    {
        $query = LwaLogs::forTenant($tenantId);
        if ($from !== null && $from !== '') {
            $query->where('created_at', '>=', $from);
        }
        if ($to !== null && $to !== '') {
            $query->where('created_at', '<=', $to);
        }

        $rows = $query->orderByDesc('created_at')->limit(self::CAP + 1)->get([
            'path', 'session_id', 'ip_address', 'created_at',
        ])->reverse()->values();

        return $rows->map(fn (LwaLogs $log): array => [
            'path'    => (string) ($log->path ?: '/'),
            'session' => (string) ($log->session_id ?: $log->ip_address ?: 'anonymous'),
            'at'      => $log->created_at ? $log->created_at->getTimestamp() : 0,
        ])->all();
    }

    /**
     * @param array<int, array{path: string, session: string, at: int}> $rows
     * @return array<string, mixed>
     */
    private function aggregate(array $rows, ?string $path): array
    {
        $sessions = [];
        foreach ($rows as $row) {
            $sessions[$row['session']][] = $row;
        }

        $viewsByPath = [];
        $visitorsByPath = [];
        $entriesByPath = [];
        $bouncedEntriesByPath = [];
        $selectedVisitors = 0;
        $selectedViews = 0;
        $entries = 0;
        $exits = 0;
        $bouncedEntries = 0;
        $nextPaths = [];
        $previousPaths = [];

        foreach ($sessions as $sessionRows) {
            usort($sessionRows, fn (array $a, array $b): int => $a['at'] <=> $b['at']);
            $first = $sessionRows[0]['path'];
            $last = $sessionRows[count($sessionRows) - 1]['path'];
            $sessionPaths = [];

            foreach ($sessionRows as $index => $row) {
                $rowPath = $row['path'];
                $sessionPaths[$rowPath] = true;
                $viewsByPath[$rowPath] = ($viewsByPath[$rowPath] ?? 0) + 1;
            }
            foreach (array_keys($sessionPaths) as $rowPath) {
                $visitorsByPath[$rowPath] = ($visitorsByPath[$rowPath] ?? 0) + 1;
            }

            $entriesByPath[$first] = ($entriesByPath[$first] ?? 0) + 1;
            if (count($sessionRows) === 1) {
                $bouncedEntriesByPath[$first] = ($bouncedEntriesByPath[$first] ?? 0) + 1;
            }

            if ($path !== null && $path !== '' && $first === $path) {
                $entries++;
                if (count($sessionRows) === 1) {
                    $bouncedEntries++;
                }
            }
            if ($path !== null && $path !== '' && $last === $path) {
                $exits++;
            }

            foreach ($sessionRows as $index => $row) {
                if ($path === null || $path === '' || $row['path'] !== $path) {
                    continue;
                }
                $selectedViews++;
                if (isset($sessionRows[$index + 1])) {
                    $next = $sessionRows[$index + 1]['path'];
                    $nextPaths[$next] = ($nextPaths[$next] ?? 0) + 1;
                }
                if ($index > 0) {
                    $previous = $sessionRows[$index - 1]['path'];
                    $previousPaths[$previous] = ($previousPaths[$previous] ?? 0) + 1;
                }
            }
            if ($path !== null && $path !== '' && isset($sessionPaths[$path])) {
                $selectedVisitors++;
            }
        }

        $totalSessions = count($sessions);
        $entryRate = $totalSessions > 0 ? round($entries / $totalSessions * 100, 2) : 0.0;
        $exitRate = $totalSessions > 0 ? round($exits / $totalSessions * 100, 2) : 0.0;
        $bounceRate = $entries > 0 ? round($bouncedEntries / $entries * 100, 2) : 0.0;

        return [
            'visitors'      => $selectedVisitors,
            'views'         => $selectedViews,
            'sessions'      => $totalSessions,
            'entries'       => $entries,
            'exits'         => $exits,
            'entry_rate'    => $entryRate,
            'exit_rate'     => $exitRate,
            'bounce_rate'   => $bounceRate,
            'next_paths'    => $this->rank($nextPaths),
            'previous_paths' => $this->rank($previousPaths),
            '_views_by_path' => $viewsByPath,
            '_visitors_by_path' => $visitorsByPath,
            '_entries_by_path' => $entriesByPath,
            '_bounced_by_path' => $bouncedEntriesByPath,
        ];
    }

    /**
     * @param array<int, array{path: string, session: string, at: int}> $rows
     * @return array<int, array<string, mixed>>
     */
    private function pathRows(array $rows): array
    {
        $aggregate = $this->aggregate($rows, null);
        $views = $aggregate['_views_by_path'];
        $visitors = $aggregate['_visitors_by_path'];
        $entries = $aggregate['_entries_by_path'];
        $bounced = $aggregate['_bounced_by_path'];

        $result = [];
        foreach ($views as $path => $count) {
            $entryCount = (int) ($entries[$path] ?? 0);
            $result[] = [
                'path'        => (string) $path,
                'views'       => (int) $count,
                'visitors'    => (int) ($visitors[$path] ?? 0),
                'bounce_rate' => $entryCount > 0
                    ? round(((int) ($bounced[$path] ?? 0)) / $entryCount * 100, 2)
                    : 0.0,
            ];
        }
        usort($result, fn (array $a, array $b): int => $b['views'] <=> $a['views']);

        return array_slice($result, 0, 50);
    }

    /** @param array<string, int> $values @return array<int, array<string, mixed>> */
    private function rank(array $values): array
    {
        arsort($values);
        $rows = [];
        foreach ($values as $path => $count) {
            $rows[] = ['path' => (string) $path, 'count' => (int) $count];
        }

        return array_slice($rows, 0, 10);
    }

    /** @param array<string, mixed> $summary @return array<string, mixed> */
    private function comparisonSummary(array $summary): array
    {
        return [
            'total_visitors' => $summary['visitors'],
            'pageviews'      => $summary['views'],
            'sessions'       => $summary['entries'],
            'bounce_rate'    => $summary['bounce_rate'],
        ];
    }

    /** @return array<string, string> */
    private function definitions(): array
    {
        return [
            'source'      => 'lwa_logs rows in the selected window, bounded to the 20,000 most recent requests.',
            'visitor'     => 'A visitor proxy is session_id, falling back to the anonymised IP address.',
            'bounce'      => 'Sessions whose first request in the window is this path and which have exactly one request in the window.',
            'entry'       => 'A session whose first recorded request in the window is this path.',
            'exit'        => 'A session whose last recorded request in the window is this path.',
            'rates'       => 'Entry/exit rates use all sessions in the window; bounce rate uses entries for this path as the denominator.',
            'next_paths'  => 'Paths recorded immediately after this path within the same session.',
            'previous'    => 'Paths recorded immediately before this path within the same session.',
            'comparison'  => 'Count deltas are relative percent; bounce-rate delta is percentage points.',
        ];
    }
}
