<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Data\FunnelQuery;
use Nafiur\WebAnalytics\Data\InsightQuery;
use Nafiur\WebAnalytics\Data\PathQuery;
use Nafiur\WebAnalytics\Data\RetentionQuery;
use Nafiur\WebAnalytics\Models\Analytics\Insight;

/**
 * Renders saved lwa_insights reports as custom-dashboard widgets. Reports are
 * always resolved inside the dashboard's website/tenant scope, and every
 * result is produced by the same documented engine used by the full report
 * pages and REST API. Only a bounded number of widgets are computed per page.
 */
final class DashboardInsights
{
    public const MAX_WIDGETS = 12;

    private const TYPE_LABELS = [
        'trends'    => 'Trend',
        'funnel'    => 'Funnel',
        'retention' => 'Retention',
        'paths'     => 'Paths',
    ];

    private const TYPE_ROUTES = [
        'trends'    => 'lwa_trends',
        'funnel'    => 'lwa_funnels',
        'retention' => 'lwa_retention',
        'paths'     => 'lwa_paths',
    ];

    /** @return array<int, array{id: int, name: string, type: string, type_label: string}> */
    public function reports(?int $websiteId, ?string $tenantId): array
    {
        if (! Schema::hasTable('lwa_insights')) {
            return [];
        }

        return Insight::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->latest()
            ->limit(100)
            ->get(['id', 'name', 'config'])
            ->map(fn (Insight $report): array => [
                'id'         => (int) $report->id,
                'name'       => (string) $report->name,
                'type'       => $this->type($report->config ?? []),
                'type_label' => self::TYPE_LABELS[$this->type($report->config ?? [])],
            ])->values()->all();
    }

    /**
     * Results are keyed by widget position so duplicate reports and skipped
     * widgets get their own message. The dashboard's time window always wins
     * over the window saved with the report, so one dashboard stays coherent.
     *
     * @param array<int, array<string, mixed>> $widgets
     * @return array<int, array<string, mixed>>
     */
    public function results(
        ?int $websiteId,
        ?string $tenantId,
        array $widgets,
        string $range,
        ?string $startDate = null,
        ?string $endDate = null
    ): array
    {
        $indexes = [];
        foreach ($widgets as $index => $widget) {
            if ($this->isInsightWidget(is_array($widget) ? $widget : [])) {
                $indexes[] = (int) $index;
            }
        }

        $computed = array_slice($indexes, 0, self::MAX_WIDGETS);
        $ids = [];
        foreach ($computed as $index) {
            $id = $this->insightId($widgets[$index]);
            if ($id !== null) {
                $ids[$id] = $id;
            }
        }

        $reports = collect();
        if ($ids !== [] && Schema::hasTable('lwa_insights')) {
            $reports = Insight::query()
                ->forWebsite($websiteId)
                ->forTenant($tenantId)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
        }

        $advanced = DatabaseReadiness::advancedReady();
        $results = [];

        foreach ($indexes as $position => $index) {
            if ($position >= self::MAX_WIDGETS) {
                $results[$index] = $this->unavailable('Insight widget limit reached: only the first ' . self::MAX_WIDGETS . ' insight widgets are computed.');

                continue;
            }

            if (! $advanced) {
                $results[$index] = $this->unavailable('Event store unavailable: enable the advanced schema to render saved reports.');

                continue;
            }

            $id = $this->insightId($widgets[$index]);
            if ($id === null) {
                $results[$index] = $this->unavailable('This widget has no saved report id.');

                continue;
            }

            $report = $reports->get($id);
            if ($report === null) {
                $results[$index] = $this->unavailable('Saved report unavailable: it was deleted or belongs to another website/tenant.');

                continue;
            }

            $results[$index] = $this->run($report, [
                'range'      => $range,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ], $websiteId);
        }

        return $results;
    }

    /**
     * @param Insight $report
     * @param array<string, mixed> $dashboardWindow
     */
    private function run(Insight $report, array $dashboardWindow, ?int $websiteId): array
    {
        $config = is_array($report->config) ? $report->config : [];
        $type = $this->type($config);

        [$from, $to] = AnalyticsWindow::resolve(array_filter([
            'range'      => $dashboardWindow['range'] ?? '7d',
            'start_date' => $dashboardWindow['start_date'] ?? null,
            'end_date'   => $dashboardWindow['end_date'] ?? null,
        ], fn ($value): bool => $value !== null && $value !== ''));

        $params = array_merge($config, [
            'website_id' => (int) $websiteId,
            'from'       => $from,
            'to'         => $to,
            'name'       => $config['event'] ?? $config['name'] ?? null,
        ]);

        $data = match ($type) {
            'funnel'    => app(FunnelService::class)->funnel(FunnelQuery::fromArray($params)),
            'retention' => app(RetentionService::class)->retention(RetentionQuery::fromArray($params)),
            'paths'     => app(PathService::class)->paths(PathQuery::fromArray($params)),
            default     => app(InsightService::class)->trend(InsightQuery::fromArray($params)),
        };

        return [
            'id'            => (int) $report->id,
            'name'          => (string) $report->name,
            'type'          => $type,
            'type_label'    => self::TYPE_LABELS[$type],
            'headline'      => $this->headline($type, $data),
            'headline_label'=> $this->headlineLabel($type, $data, $config),
            'rows'          => $this->rows($type, $data),
            'sample_size'   => (int) ($data['sample_size'] ?? 0),
            'capped'        => (bool) ($data['capped'] ?? false),
            'definitions'   => $this->definitions($data),
            'url'           => $this->url($report, $config, $type),
        ];
    }

    /** @param array<string, mixed> $data */
    private function headline(string $type, array $data): string
    {
        return match ($type) {
            'funnel'    => $this->percent($data['overall_conversion_rate'] ?? null),
            'retention' => $this->percent($data['summary']['period_one_rate'] ?? null),
            'paths'     => $this->percent($data['summary']['top_path_share'] ?? null),
            default     => $this->number($data['global_total'] ?? 0),
        };
    }

    /** @param array<string, mixed> $data @param array<string, mixed> $config */
    private function headlineLabel(string $type, array $data, array $config): string
    {
        return match ($type) {
            'funnel'    => 'Overall conversion · ' . $this->number($data['completed_users'] ?? 0) . ' of ' . $this->number($data['entered_users'] ?? 0) . ' users',
            'retention' => 'Period 1 retention · ' . $this->number($data['summary']['cohorts'] ?? 0) . ' cohorts',
            'paths'     => 'Top path share · ' . $this->number($data['summary']['sequenced_users'] ?? 0) . ' sequenced users',
            default     => 'Total ' . (string) ($config['metric'] ?? 'events') . ' across all buckets',
        };
    }

    /** @param array<string, mixed> $data @return array<int, array{label: string, value: string}> */
    private function rows(string $type, array $data): array
    {
        $rows = [];

        if ($type === 'trends') {
            foreach (array_slice($data['totals'] ?? [], 0, 6) as $total) {
                $rows[] = [
                    'label' => (string) ($total['name'] ?? 'Total'),
                    'value' => $this->number($total['value'] ?? 0),
                ];
            }
        } elseif ($type === 'funnel') {
            foreach (array_slice($data['steps'] ?? [], 0, 6) as $step) {
                $rows[] = [
                    'label' => 'Step ' . (string) ($step['step'] ?? '') . ' · ' . (string) ($step['event'] ?? ''),
                    'value' => $this->number($step['users'] ?? 0) . ' · ' . $this->percent($step['overall_conversion_rate'] ?? null),
                ];
            }
        } elseif ($type === 'retention') {
            foreach (array_slice($data['cohorts'] ?? [], 0, 6) as $cohort) {
                $rows[] = [
                    'label' => (string) ($cohort['label'] ?? '') . ' · ' . $this->number($cohort['users'] ?? 0) . ' users',
                    'value' => $this->percent($cohort['cells'][1]['rate'] ?? null),
                ];
            }
        } else {
            foreach (array_slice($data['paths'] ?? [], 0, 6) as $path) {
                $rows[] = [
                    'label' => implode(' → ', array_map('strval', $path['steps'] ?? [])),
                    'value' => $this->number($path['users'] ?? 0) . ' · ' . $this->percent($path['share'] ?? null),
                ];
            }
        }

        return $rows;
    }

    /** @param array<string, mixed> $data @return array<int, string> */
    private function definitions(array $data): array
    {
        $notes = $data['definitions'] ?? [];
        if (! is_array($notes)) {
            return [];
        }

        $compact = [];
        $first = (string) ($notes[0] ?? '');
        if ($first !== '') {
            $compact[] = $first;
        }
        if (! empty($data['capped'])) {
            $last = (string) ($notes[array_key_last($notes)] ?? '');
            if ($last !== '' && $last !== $first) {
                $compact[] = $last;
            }
        }

        return $compact;
    }

    /** @param array<string, mixed> $config */
    private function url(Insight $report, array $config, string $type): string
    {
        $url = route(self::TYPE_ROUTES[$type] ?? 'lwa_trends');
        $query = array_diff_key($config, ['type' => true]);

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /** @param array<string, mixed> $config */
    private function type(array $config): string
    {
        $type = strtolower((string) ($config['type'] ?? 'trends'));

        return isset(self::TYPE_LABELS[$type]) ? $type : 'trends';
    }

    /** @param array<string, mixed> $widget */
    private function isInsightWidget(array $widget): bool
    {
        return strtolower((string) ($widget['type'] ?? '')) === 'insight'
            || $this->insightId($widget) !== null
            || str_starts_with((string) ($widget['widget'] ?? ''), 'insight:');
    }

    /** @param array<string, mixed> $widget */
    private function insightId(array $widget): ?int
    {
        $id = (int) ($widget['insight_id'] ?? 0);
        if ($id > 0) {
            return $id;
        }

        if (preg_match('/^insight:(\d+)$/', (string) ($widget['widget'] ?? ''), $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function number(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value);
    }

    private function percent(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value, 1) . '%';
    }

    /** @return array{unavailable: string} */
    private function unavailable(string $message): array
    {
        return ['unavailable' => $message];
    }
}
