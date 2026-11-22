<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;

class ExportAnalyticsCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:export'];

    protected $signature = 'lwa:export
                            {--format=csv : Export format (csv only for now)}
                            {--range=24h : Time range to export}
                            {--tenant= : Optional tenant ID}
                            {--output= : Write to a file instead of stdout}';

    protected $description = 'Export analytics telemetry to CSV';

    public function handle(): int
    {
        $format = strtolower((string) $this->option('format'));
        if ($format !== 'csv') {
            $this->error("Unsupported format [{$format}]. Only 'csv' is available.");
            return self::FAILURE;
        }

        $range = (string) $this->option('range');
        $tenantId = $this->option('tenant') ?: null;
        $cutoff = (new DatabaseTrafficAnalytics())->resolveCutoffTime($range, null, null);

        $query = LwaLogs::forTenant($tenantId);
        if ($cutoff['start']) {
            $query->where('created_at', '>=', $cutoff['start']);
        }
        if ($cutoff['end']) {
            $query->where('created_at', '<=', $cutoff['end']);
        }

        $outputPath = $this->option('output');
        $handle = $outputPath ? fopen($outputPath, 'w') : fopen('php://output', 'w');
        if (!$handle) {
            $this->error('Could not open output stream.');
            return self::FAILURE;
        }

        fputcsv($handle, ['ID', 'Tenant ID', 'IP Address', 'Method', 'Path', 'Status', 'Response Time (ms)', 'Bytes', 'Browser', 'OS', 'Device', 'Country', 'UTM Source', 'UTM Medium', 'UTM Campaign', 'Date Time']);

        $count = 0;
        $query->chunkById(500, function ($logs) use ($handle, &$count) {
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id, $log->tenant_id, $log->ip_address, $log->method, $log->path,
                    $log->status_code, $log->response_time_ms, $log->bytes_sent,
                    $log->browser, $log->os, $log->device, $log->country,
                    $log->utm_source, $log->utm_medium, $log->utm_campaign, $log->created_at,
                ]);
                $count++;
            }
        });

        if ($outputPath) {
            fclose($handle);
            $this->info("Exported {$count} row(s) to {$outputPath}.");
        } else {
            $this->newLine();
            $this->info("Exported {$count} row(s).");
        }

        return self::SUCCESS;
    }
}
