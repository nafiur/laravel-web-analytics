<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\LwaLogs;

class PruneAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lwa:prune {--days= : The number of days to retain logs} {--tenant= : Optional tenant ID to prune logs for a specific tenant}';

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
        $tenantId = $this->option('tenant');

        $msg = "Pruning web analytics logs older than {$days} days";
        if ($tenantId) {
            $msg .= " for tenant [{$tenantId}]";
        }
        $this->info($msg . '...');

        $query = LwaLogs::where('created_at', '<', now()->subDays($days));
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->delete();

        $this->info("Successfully deleted {$count} outdated log record(s).");

        return self::SUCCESS;
    }
}
