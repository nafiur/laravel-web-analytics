<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Services\AlertService;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;

class WeeklySummaryCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:summary'];

    protected $signature = 'lwa:summary {--range=7d : Summary range} {--tenant= : Optional tenant ID}';

    protected $description = 'Send a periodic traffic summary through the configured alert channel';

    public function handle(): int
    {
        $range = (string) $this->option('range');
        $tenantId = $this->option('tenant') ?: null;

        $summary = (new DatabaseTrafficAnalytics())->getDashboardData($range, null, null, $tenantId)['summary'];

        $this->table(['Metric', 'Value'], [
            ['Requests', $summary['total_requests_fmt'] ?? 0],
            ['Visitors', $summary['unique_visitors_fmt'] ?? 0],
            ['Bandwidth', $summary['total_bytes_fmt'] ?? '0 B'],
            ['Avg response', ($summary['avg_response_time'] ?? 0) . ' ms'],
            ['Error rate', ($summary['error_rate'] ?? 0) . '%'],
        ]);

        if (AlertService::sendSummary($summary)) {
            $this->info('Summary sent through the configured alert channel.');
        } else {
            $this->comment('Alerts are disabled — summary printed only.');
        }

        return self::SUCCESS;
    }
}
