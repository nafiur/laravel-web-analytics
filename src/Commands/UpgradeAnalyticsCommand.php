<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Services\UpgradeService;

class UpgradeAnalyticsCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:upgrade'];

    protected $signature = 'lwa:upgrade
                            {--dry-run : Count rows without writing anything}
                            {--chunk=1000 : Rows per chunk}
                            {--website= : Existing website ID to import into}
                            {--website-domain= : Domain for the auto-created website}
                            {--since= : Only import logs from this date}
                            {--prune-legacy : Delete legacy rows that were imported}';

    protected $description = 'Backfill legacy lwa_logs into the advanced analytics schema';

    public function handle(UpgradeService $upgrade): int
    {
        $missing = \Nafiur\WebAnalytics\Services\SchemaGuard::missing();
        if (!empty($missing)) {
            $this->error('Advanced analytics tables are missing: ' . implode(', ', $missing));
            $this->line('Enable the schema and migrate before running the upgrade:');
            $this->line('  WEB_ANALYTICS_SCHEMA_ENABLED=true');
            $this->line('  php artisan optimize:clear');
            $this->line('  php artisan migrate');
            return self::FAILURE;
        }

        $result = $upgrade->run([
            'dry_run'        => (bool) $this->option('dry-run'),
            'chunk'          => (int) $this->option('chunk'),
            'website_id'     => $this->option('website'),
            'website_domain' => $this->option('website-domain'),
            'since'          => $this->option('since'),
            'prune_legacy'   => (bool) $this->option('prune-legacy'),
        ]);

        if (isset($result['error'])) {
            $this->error($result['error']);
            return self::FAILURE;
        }

        $this->table(['Metric', 'Value'], collect($result)
            ->map(fn ($value, $key) => [$key, (string) $value])
            ->values()
            ->all());

        if ($result['dry_run'] ?? false) {
            $this->comment('Dry run — no data was written. "read" is the total legacy row count; '
                . '"pending_since_id" is what the next real run will import.');
        } else {
            $this->info('Upgrade backfill complete. Re-run to process any new legacy rows.');
        }

        return self::SUCCESS;
    }
}
