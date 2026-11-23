<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Services\PreflightService;

class DoctorCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:doctor'];

    protected $signature = 'lwa:doctor {--json : Output the report as JSON}';

    protected $description = 'Check installation readiness before upgrading or enabling analytics';

    public function handle(PreflightService $doctor): int
    {
        $report = $doctor->run();
        $summary = $report['summary'];

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
