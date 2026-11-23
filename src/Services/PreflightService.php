<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;
use Nafiur\WebAnalytics\Models\LwaLogs;

/**
 * Pre-upgrade / pre-flight diagnostics. Every check is isolated so one bad
 * configuration cannot prevent the rest of the report from being produced.
 */
class PreflightService
{
    /** @return array{checks: array<int, array<string, string>>, summary: array<string, int|string>} */
    public function run(): array
    {
        $checks = [];

        $checks[] = $this->check('PHP version', version_compare(PHP_VERSION, '8.2', '>=') ? 'ok' : 'warn',
            'Running ' . PHP_VERSION . ' (v3 target: 8.2+)');

        $laravel = app()->version();
        $checks[] = $this->check('Laravel version', version_compare($laravel, '11.0', '>=') ? 'ok' : 'warn',
            'Running ' . $laravel . ' (v3 target: 11+)');

        $checks[] = $this->check('Required extensions',
            extension_loaded('pdo') && extension_loaded('mbstring') ? 'ok' : 'fail',
            'pdo=' . ($this->bool(extension_loaded('pdo'))) . ', mbstring=' . $this->bool(extension_loaded('mbstring')));

        $checks[] = $this->databaseCheck();
        $checks[] = $this->legacyCheck();
        $checks[] = $this->advancedCheck();
        $checks[] = $this->configCacheCheck();
        $checks[] = $this->schemaFlagCheck();
        $checks[] = $this->queueCheck();
        $checks[] = $this->cacheCheck();
        $checks[] = $this->storageCheck();
        $checks[] = $this->publishedViewsCheck();
        $checks[] = $this->upgradeStateCheck();

        $failures = count(array_filter($checks, fn ($c) => $c['status'] === 'fail'));
        $warnings = count(array_filter($checks, fn ($c) => $c['status'] === 'warn'));

        $missingTables = false;
        foreach ($checks as $check) {
            if (str_contains($check['detail'], 'Missing:')) {
                $missingTables = true;
                break;
            }
        }

        $next = $failures > 0
            ? ($missingTables
                ? 'Run php artisan migrate, then re-run php artisan lwa:doctor'
                : 'Fix the failed checks, then re-run php artisan lwa:doctor')
            : 'Safe to run: php artisan lwa:upgrade --dry-run';

        return [
            'checks'  => $checks,
            'summary' => [
                'status'   => $failures > 0 ? 'fail' : ($warnings > 0 ? 'warn' : 'ok'),
                'passed'   => count(array_filter($checks, fn ($c) => $c['status'] === 'ok')),
                'warnings' => $warnings,
                'failures' => $failures,
                'next'     => $next,
            ],
        ];
    }

    /** @return array<string, string> */
    private function check(string $label, string $status, string $detail): array
    {
        return ['check' => $label, 'status' => $status, 'detail' => $detail];
    }

    private function databaseCheck(): array
    {
        try {
            $driver = DB::connection()->getDriverName();
            DB::connection()->getPdo();

            return $this->check('Database connection', 'ok', 'Connected via ' . $driver);
        } catch (\Throwable $e) {
            return $this->check('Database connection', 'fail', $e->getMessage());
        }
    }

    private function legacyCheck(): array
    {
        try {
            if (!DatabaseReadiness::legacyReady()) {
                return $this->check('Legacy telemetry table', 'fail',
                    'Missing ' . DatabaseReadiness::legacyTable() . ' — run php artisan migrate');
            }

            $count = LwaLogs::count();
            $oldest = LwaLogs::min('created_at');
            $newest = LwaLogs::max('created_at');

            return $this->check('Legacy telemetry table', 'ok',
                DatabaseReadiness::legacyTable() . ": {$count} row(s), "
                . ($oldest ? "from {$oldest} to {$newest}" : 'no timestamps yet'));
        } catch (\Throwable $e) {
            return $this->check('Legacy telemetry table', 'fail', $e->getMessage());
        }
    }

    private function advancedCheck(): array
    {
        $missing = SchemaGuard::missing();

        return empty($missing)
            ? $this->check('Advanced analytics tables', 'ok', 'All ' . count(SchemaGuard::REQUIRED_TABLES) . ' tables present')
            : $this->check('Advanced analytics tables', 'fail', 'Missing: ' . implode(', ', $missing));
    }

    private function configCacheCheck(): array
    {
        $cached = file_exists(base_path('bootstrap/cache/config.php'));

        return $cached
            ? $this->check('Config cache', 'warn', 'Config is cached — run php artisan optimize:clear after changing .env')
            : $this->check('Config cache', 'ok', 'Not cached');
    }

    private function schemaFlagCheck(): array
    {
        $env = filter_var(env('WEB_ANALYTICS_SCHEMA_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $config = (bool) config('laravel-web-analytics.analytics.enabled', false);

        return ($env || $config)
            ? $this->check('Schema flag', 'ok', 'WEB_ANALYTICS_SCHEMA_ENABLED is enabled')
            : $this->check('Schema flag', 'warn', 'Disabled — advanced collector/API are inactive');
    }

    private function queueCheck(): array
    {
        $mode = config('laravel-web-analytics.analytics.mode', 'sync');
        $driver = config('queue.default', 'sync');

        if ($mode === 'queue' && $driver === 'sync') {
            return $this->check('Collector mode', 'warn', 'mode=queue but queue.default=sync — jobs will run inline');
        }

        return $this->check('Collector mode', 'ok', 'mode=' . $mode . ', queue=' . $driver);
    }

    private function cacheCheck(): array
    {
        $driver = config('cache.default', 'array');

        return $driver === 'array'
            ? $this->check('Cache driver', 'warn', 'array cache is per-process — use file/redis in production')
            : $this->check('Cache driver', 'ok', 'driver=' . $driver);
    }

    private function storageCheck(): array
    {
        try {
            $path = storage_path();
            return is_writable($path)
                ? $this->check('Storage writable', 'ok', $path)
                : $this->check('Storage writable', 'warn', $path . ' is not writable');
        } catch (\Throwable $e) {
            return $this->check('Storage writable', 'warn', $e->getMessage());
        }
    }

    private function publishedViewsCheck(): array
    {
        $published = resource_path('views/vendor/web-analytics');
        if (!is_dir($published)) {
            return $this->check('Published views', 'ok', 'Using package views');
        }

        return $this->check('Published views', 'warn',
            'Published copy found — run vendor:publish --tag=web-analytics-views --force after upgrades');
    }

    private function upgradeStateCheck(): array
    {
        try {
            if (in_array('lwa_upgrade_checkpoints', SchemaGuard::missing(), true)) {
                return $this->check('Upgrade checkpoint', 'warn', 'Waiting for advanced tables — run php artisan migrate');
            }

            $checkpoint = AnalyticsModels\UpgradeCheckpoint::where('name', 'legacy_logs')->first();
            if ($checkpoint === null) {
                return $this->check('Upgrade checkpoint', 'warn', 'No upgrade has been run yet');
            }

            $pending = LwaLogs::where('id', '>', $checkpoint->last_id)->count();

            return $pending > 0
                ? $this->check('Upgrade checkpoint', 'warn', "last_id={$checkpoint->last_id}, {$pending} legacy row(s) pending")
                : $this->check('Upgrade checkpoint', 'ok', "last_id={$checkpoint->last_id}, no pending rows");
        } catch (\Throwable $e) {
            return $this->check('Upgrade checkpoint', 'warn', $e->getMessage());
        }
    }

    private function bool(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
