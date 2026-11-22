<?php

namespace Nafiur\WebAnalytics\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AggregationService;

class AggregateAnalyticsCommand extends Command
{
    protected $signature = 'lwa:aggregate
                            {--website= : Website ID (defaults to all websites)}
                            {--from= : Start date/time}
                            {--to= : End date/time}
                            {--granularity=hourly : hourly or daily}';

    protected $description = 'Build hourly/daily analytics rollups from raw events';

    public function handle(AggregationService $aggregation): int
    {
        $granularity = $this->option('granularity') === 'daily' ? 'daily' : 'hourly';
        $from = $this->option('from') ? Carbon::parse($this->option('from')) : now()->subDays(2);
        $to = $this->option('to') ? Carbon::parse($this->option('to')) : now();

        $websiteId = $this->option('website');
        $websites = $websiteId
            ? Website::where('id', (int) $websiteId)->get()
            : Website::all();

        if ($websites->isEmpty()) {
            $this->warn('No websites found.');
            return self::SUCCESS;
        }

        $total = 0;
        foreach ($websites as $website) {
            $count = $aggregation->aggregateRange($website->id, $from, $to, $granularity);
            $total += $count;
            $this->line("{$website->domain}: {$count} {$granularity} bucket(s)");
        }

        $this->info("Aggregated {$total} bucket(s).");

        return self::SUCCESS;
    }
}
