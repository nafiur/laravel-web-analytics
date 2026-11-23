<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Schema;

class SchemaGuard
{
    /** Tables required by the advanced analytics collector/reporting layer. */
    public const REQUIRED_TABLES = [
        'lwa_websites',
        'lwa_visitors',
        'lwa_sessions',
        'lwa_pageviews',
        'lwa_events',
        'lwa_hourly_stats',
        'lwa_daily_stats',
        'lwa_upgrade_checkpoints',
    ];

    /** @return string[] */
    public static function missing(): array
    {
        return array_values(array_filter(self::REQUIRED_TABLES, function (string $table) {
            try {
                return !Schema::hasTable($table);
            } catch (\Throwable $e) {
                return true;
            }
        }));
    }
}
