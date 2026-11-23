<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Services\PruneService;

class PruneAnalyticsCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:prune'];

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
    public function handle(PruneService $prune): int
    {
        if (!\Nafiur\WebAnalytics\Services\DatabaseReadiness::legacyReady()) {
            $this->error('Legacy table ' . \Nafiur\WebAnalytics\Services\DatabaseReadiness::legacyTable() . ' does not exist.');
            $this->line('Run: php artisan migrate');
            return self::FAILURE;
        }

        $daysOption = $this->option('days');
        $days = max(1, (int) ($daysOption !== null && $daysOption !== ''
            ? $daysOption
            : config('laravel-web-analytics.retention_days', 30)));
        $tenantId = $this->option('tenant');

        $msg = "Pruning web analytics logs older than {$days} days";
        if ($tenantId) {
            $msg .= " for tenant [{$tenantId}]";
        }
        $this->info($msg . '...');

        $count = $prune->prune($days, $tenantId);

        $this->info("Successfully deleted {$count} outdated log record(s).");

        return self::SUCCESS;
    }
}
