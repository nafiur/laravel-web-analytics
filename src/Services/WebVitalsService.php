<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;

/**
 * Aggregates numeric `web_vital` events from lwa_events. Values are recorded
 * by the build-free browser SDK; nothing is inferred from request timings.
 */
class WebVitalsService
{
    private const CAP = 20000;
    private const METRICS = [
        'lcp' => ['label' => 'Largest Contentful Paint', 'unit' => 'ms', 'good' => 2500, 'poor' => 4000],
        'cls' => ['label' => 'Cumulative Layout Shift', 'unit' => 'score', 'good' => 0.1, 'poor' => 0.25],
        'inp' => ['label' => 'Interaction to Next Paint', 'unit' => 'ms', 'good' => 200, 'poor' => 500],
    ];

    public function __construct(private EventStore $events)
    {
    }

    /** @return array<string, mixed> */
    public function summarize(?int $websiteId, string $range, ?string $path = null): array
    {
        [$from, $to] = $this->window($range);
        $query = new EventQuery(
            websiteId: (int) $websiteId,
            name: 'web_vital',
            from: $from,
            to: $to,
            limit: self::CAP,
        );
        $events = iterator_to_array($this->events->query($query));

        $values = [];
        $byPath = [];
        foreach ($events as $event) {
            $properties = $event->properties ?? [];
            $metric = (string) ($properties['metric'] ?? '');
            if (! isset(self::METRICS[$metric]) || ! is_numeric($properties['value'] ?? null)) {
                continue;
            }
            $value = (float) $properties['value'];
            $pathKey = (string) ($properties['path'] ?? '/');
            if ($path !== null && $path !== '' && $pathKey !== $path) {
                continue;
            }
            $values[$metric][] = $value;
            $byPath[$pathKey][$metric][] = $value;
        }

        $metrics = [];
        foreach (self::METRICS as $key => $definition) {
            $metrics[$key] = $this->metric($key, $definition, $values[$key] ?? []);
        }

        $paths = [];
        foreach ($byPath as $pathKey => $perMetric) {
            $row = ['path' => $pathKey];
            foreach (self::METRICS as $key => $definition) {
                $row[$key] = $this->metric($key, $definition, $perMetric[$key] ?? []);
            }
            $paths[] = $row;
        }
        usort($paths, fn (array $a, array $b): int => ($b['lcp']['count'] ?? 0) <=> ($a['lcp']['count'] ?? 0));

        return [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => count($events),
            'capped'      => count($events) >= self::CAP,
            'metrics'     => $metrics,
            'paths'       => array_slice($paths, 0, 50),
            'definitions' => [
                'source'     => 'lwa_events rows named web_vital, emitted by the browser SDK when web vitals are enabled.',
                'sample'     => 'At most 20,000 most recent web_vital events are read; counts are lower bounds when the cap is reached.',
                'p75'        => '75th percentile (nearest-rank) of recorded values for that metric and window.',
                'thresholds' => 'LCP: good ≤ 2500 ms, poor > 4000 ms. CLS: good ≤ 0.1, poor > 0.25. INP: good ≤ 200 ms, poor > 500 ms.',
                'attribution' => 'The path comes from the event property emitted by the SDK; no path is inferred from pageviews.',
                'privacy'    => 'Only numeric metric values and URL paths are recorded — no form values or user text.',
            ],
        ];
    }

    /** @return array{0: string, 1: string} */
    private function window(string $range): array
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? 3650;
        $now = CarbonImmutable::now();

        return [$now->subDays($days)->toIso8601String(), $now->toIso8601String()];
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<int, float> $values
     * @return array<string, mixed>
     */
    private function metric(string $key, array $definition, array $values): array
    {
        $count = count($values);
        if ($count === 0) {
            return [
                'key' => $key, 'label' => $definition['label'], 'unit' => $definition['unit'],
                'count' => 0, 'p50' => null, 'p75' => null, 'max' => null,
                'good' => 0, 'needs_improvement' => 0, 'poor' => 0,
            ];
        }

        sort($values);
        $good = 0;
        $poor = 0;
        foreach ($values as $value) {
            if ($value <= $definition['good']) {
                $good++;
            } elseif ($value > $definition['poor']) {
                $poor++;
            }
        }

        return [
            'key'  => $key,
            'label' => $definition['label'],
            'unit'  => $definition['unit'],
            'count' => $count,
            'p50'   => round($this->percentile($values, 0.5), 3),
            'p75'   => round($this->percentile($values, 0.75), 3),
            'max'   => round($values[$count - 1], 3),
            'good'  => $good,
            'needs_improvement' => $count - $good - $poor,
            'poor'  => $poor,
        ];
    }

    /** @param array<int, float> $values */
    private function percentile(array $values, float $fraction): float
    {
        $index = max(0, (int) ceil($fraction * count($values)) - 1);

        return $values[$index];
    }
}
