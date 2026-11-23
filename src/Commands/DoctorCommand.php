<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Services\PreflightService;

class DoctorCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:doctor'];

    protected $signature = 'lwa:doctor {--json : Output the report as JSON} {--repair : Run the advanced-schema migration directly to create missing tables}';

    protected $description = 'Check installation readiness before upgrading or enabling analytics';

    public function handle(PreflightService $doctor): int
    {
        $report = $doctor->run();
        $repaired = false;

        // A migration that was previously skipped by a config guard is still
        // recorded as "run" by Laravel. Re-executing the migration file
        // directly is the safe repair — its Schema::hasTable guards make it
        // idempotent, and the legacy table is never touched.
        if ($this->option('repair') && $report['summary']['status'] === 'fail') {
            $path = __DIR__ . '/../../database/migrations/2026_11_24_000000_create_analytics_tables.php';
            if (file_exists($path)) {
                $migration = require $path;
                $migration->up();
                $repaired = true;
                $report = $doctor->run();
            } elseif (!$this->option('json')) {
                $this->output->warning('Migration file not found — update the package first.');
            }
        }

        $summary = $report['summary'];

        if ($repaired && !$this->option('json')) {
            $this->output->note('Repair applied: advanced-schema migration executed.');
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $summary['status'] === 'fail' ? self::FAILURE : self::SUCCESS;
        }

        $this->output->title('Laravel Web Analytics — Preflight Check');

        $rows = [];
        foreach ($report['checks'] as $check) {
            $rows[] = [
                $this->badge($check['status']),
                $check['check'],
                $check['detail'],
            ];
        }
        $this->output->table(['Status', 'Check', 'Detail'], $rows);

        $this->line(sprintf(
            '  <info>Passed:</info> %d   <comment>Warnings:</comment> %d   <error>Failures:</error> %d',
            $summary['passed'],
            $summary['warnings'],
            $summary['failures']
        ));
        $this->newLine();

        if ($summary['status'] === 'fail') {
            $this->output->error('Preflight failed. ' . $summary['next']);
            return self::FAILURE;
        }

        if ($summary['status'] === 'warn') {
            $this->output->warning('Preflight passed with warnings. ' . $summary['next']);
        } else {
            $this->output->success('All checks passed. ' . $summary['next']);
        }

        return self::SUCCESS;
    }

    private function badge(string $status): string
    {
        return match ($status) {
            'ok'   => '<info>OK</info>',
            'warn' => '<comment>WARN</comment>',
            default => '<error>FAIL</error>',
        };
    }
}
