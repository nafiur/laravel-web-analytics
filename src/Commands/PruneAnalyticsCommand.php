<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\LwaLogs;

class PruneAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lwa:prune {--days= : The number of days to retain logs}';

    /**
     * The console command description.
     */
    protected $description = 'Prune old web analytics logs from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('laravel-web-analytics.retention_days', 30));

        $this->info("Pruning web analytics logs older than {$days} days...");

        $count = LwaLogs::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Successfully deleted {$count} outdated log record(s).");

        return self::SUCCESS;
    }
}
