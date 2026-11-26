<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\PerformanceSpan;

class PerformanceService
{
    private const CAP = 20000;
    private const ATTRIBUTE_KEYS = ['http.method', 'http.route', 'http.status_code', 'db.operation', 'cache.status', 'release', 'environment'];

    public function __construct(private EventStore $events)
    {
    }

    /** @param array<int, array<string, mixed>> $spans @return array<string, int> */
    public function ingest(array $spans, int $websiteId, ?string $tenantId): array
    {
        $max = (int) config('laravel-web-analytics.observability.max_spans', 100);
        $spans = array_slice($spans, 0, $max);
        $inserted = 0;
        $duplicates = 0;
        $now = CarbonImmutable::now();
        $events = [];

        foreach ($spans as $span) {
            if (! is_array($span)) {
                continue;
            }
            $traceId = mb_substr(trim((string) ($span['trace_id'] ?? '')), 0, 64);
            $spanId = mb_substr(trim((string) ($span['span_id'] ?? '')), 0, 64);
            $name = mb_substr(trim((string) ($span['name'] ?? '')), 0, 150);
            if ($traceId === '' || $spanId === '' || $name === '') {
                continue;
            }

            $exists = PerformanceSpan::query()->where('trace_id', $traceId)->where('span_id', $spanId)->exists();
            if ($exists) {
                $duplicates++;
                continue;
            }

            $duration = max(0, (int) ($span['duration_ms'] ?? 0));
            $hasError = (bool) ($span['has_error'] ?? false)
                || in_array((string) ($span['status'] ?? 'ok'), ['error', 'fatal'], true);
            $attributes = [];
            foreach (is_array($span['attributes'] ?? null) ? $span['attributes'] : [] as $key => $value) {
                if (in_array((string) $key, self::ATTRIBUTE_KEYS, true) && is_scalar($value)) {
                    $attributes[(string) $key] = mb_substr((string) $value, 0, 200);
                }
            }

            PerformanceSpan::create([
                'website_id'  => $websiteId,
                'tenant_id'   => $tenantId,
                'trace_id'    => $traceId,
                'span_id'     => $spanId,
                'parent_id'   => $span['parent_id'] ?? null,
                'name'        => $name,
                'kind'        => mb_substr((string) ($span['kind'] ?? 'internal'), 0, 30),
                'start_ms'    => max(0, (int) ($span['start_ms'] ?? 0)),
                'duration_ms' => $duration,
                'status'      => in_array($span['status'] ?? '', ['ok', 'error', 'fatal'], true) ? $span['status'] : 'ok',
                'has_error'   => $hasError,
                'attributes'  => $attributes,
            ]);
            $inserted++;

            $events[] = new UnifiedEvent(
                uuid: 'span_' . substr(hash('sha256', $traceId . ':' . $spanId), 0, 40),
                name: 'span',
                timestamp: $now,
                distinctId: null,
                anonymousId: 'trace_' . substr(hash('sha256', $traceId), 0, 16),
                sessionId: null,
                websiteId: $websiteId,
                properties: [
                    'trace_hash' => hash('sha256', $traceId),
                    'name'       => $name,
                    'duration_ms' => $duration,
                    'status'     => $hasError ? 'error' : 'ok',
                ],
                context: [],
                value: (float) $duration,
                createdAt: $now,
            );
        }

        if ($events !== []) {
            $this->events->insert($events);
        }

        return ['inserted' => $inserted, 'duplicates' => $duplicates, 'received' => count($spans)];
    }

    /** @return array<string, mixed> */
    public function summarize(?int $websiteId, ?string $tenantId, string $range): array
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? 3650;
        $rows = PerformanceSpan::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->where('created_at', '>=', CarbonImmutable::now()->subDays($days))
            ->orderByDesc('id')
            ->limit(self::CAP + 1)
            ->get();
        $capped = $rows->count() > self::CAP;
        if ($capped) {
            $rows = $rows->slice(0, self::CAP);
        }

        $byName = [];
        $slowest = $rows->sortByDesc('duration_ms')->take(20);
        $traces = [];
        foreach ($rows as $row) {
            $byName[$row->name][] = (int) $row->duration_ms;
            $traces[$row->trace_id] = ($traces[$row->trace_id] ?? 0) + 1;
        }

        $operations = [];
        foreach ($byName as $name => $durations) {
            sort($durations);
            $count = count($durations);
            $operations[] = [
                'name'   => (string) $name,
                'count'  => $count,
                'p50'    => $this->percentile($durations, 0.5),
                'p95'    => $this->percentile($durations, 0.95),
                'max'    => (int) $durations[$count - 1],
                'avg'    => (int) round(array_sum($durations) / $count),
            ];
        }
        usort($operations, fn (array $a, array $b): int => $b['p95'] <=> $a['p95']);

        $total = $rows->count();
        $errors = (int) $rows->filter(fn (PerformanceSpan $row): bool => $row->has_error)->count();

        return [
            'range'        => $range,
            'sample_size'  => $total,
            'capped'       => $capped,
            'spans'        => $total,
            'traces'       => count($traces),
            'error_rate'   => $total > 0 ? round($errors / $total * 100, 2) : 0.0,
            'operations'   => array_slice($operations, 0, 50),
            'slowest'      => $slowest->map(fn (PerformanceSpan $row): array => [
                'trace_id' => $row->trace_id,
                'name'     => $row->name,
                'duration_ms' => (int) $row->duration_ms,
                'status'   => $row->status,
            ])->values()->all(),
            'definitions'  => [
                'source'    => 'lwa_performance_spans, bounded to the 20,000 most recent spans in the window.',
                'p95'       => '95th percentile of recorded span durations (nearest-rank).',
                'error'     => 'A span with status error/fatal or has_error=true.',
                'privacy'   => 'Only whitelisted scalar attributes are stored; trace ids are hashed in mirrored lwa_events rows.',
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function trace(string $traceId, ?int $websiteId, ?string $tenantId): ?array
    {
        $spans = PerformanceSpan::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->where('trace_id', $traceId)
            ->orderBy('start_ms')
            ->limit(500)
            ->get();

        if ($spans->isEmpty()) {
            return null;
        }

        return [
            'trace_id' => $traceId,
            'spans'    => $spans->map(fn (PerformanceSpan $row): array => [
                'span_id'    => $row->span_id,
                'parent_id'  => $row->parent_id,
                'name'       => $row->name,
                'kind'       => $row->kind,
                'start_ms'   => (int) $row->start_ms,
                'duration_ms' => (int) $row->duration_ms,
                'status'     => $row->status,
                'attributes' => $row->attributes ?? [],
            ])->all(),
        ];
    }

    /** @param array<int, int> $values */
    private function percentile(array $values, float $fraction): int
    {
        $index = max(0, (int) ceil($fraction * count($values)) - 1);

        return (int) $values[$index];
    }
}
