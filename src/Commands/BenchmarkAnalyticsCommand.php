<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;
use Nafiur\WebAnalytics\Services\MetricService;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

/**
 * Read-only timing comparison: aggregate-based reporting vs a raw scan.
 * Useful when validating rollups on a production-sized dataset.
 */
class BenchmarkAnalyticsCommand extends Command
{
    protected $signature = 'lwa:benchmark {--range=24h} {--website=}';

    protected $description = 'Compare aggregate reporting time with a raw pageview scan';

    public function handle(MetricService $metrics, WebsiteResolver $resolver): int
    {
        $missing = \Nafiur\WebAnalytics\Services\SchemaGuard::missing();
        if (!empty($missing)) {
            $this->error('Advanced analytics tables are missing: ' . implode(', ', $missing));
            $this->line('Run: WEB_ANALYTICS_SCHEMA_ENABLED=true, then php artisan migrate');
            return self::FAILURE;
        }

        $websiteId = (int) ($this->option('website') ?: $resolver->currentId() ?: 0);
        if ($websiteId <= 0) {
            $this->error('No website found. Register one first.');
            return self::FAILURE;
        }

        $range = (string) $this->option('range');

        $start = microtime(true);
        $summary = $metrics->summary($websiteId, $range);
        $series = $metrics->timeseries($websiteId, $range);
        $aggregateMs = round((microtime(true) - $start) * 1000, 2);

        [$from, $to] = $metrics->resolveRange($range);
        $start = microtime(true);
        $rawCount = AnalyticsModels\Pageview::withoutGlobalScope(\Nafiur\WebAnalytics\Services\WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->count();
        $rawMs = round((microtime(true) - $start) * 1000, 2);

        $this->table(['Metric', 'Value'], [
            ['Website ID', $websiteId],
            ['Range', $range],
            ['Aggregate summary + series', $aggregateMs . ' ms'],
            ['Raw pageview scan', $rawMs . ' ms'],
            ['Raw rows in range', $rawCount],
            ['Rows in rollups', count($series['requests'] ?? [])],
            ['Reported requests', $summary['total_requests'] ?? 0],
        ]);

        $this->comment('Benchmark is read-only. Run lwa:aggregate first for meaningful rollups.');

        return self::SUCCESS;
    }
}
