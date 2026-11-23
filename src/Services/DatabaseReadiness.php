<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Schema;

class DatabaseReadiness
{
    public static function legacyTable(): string
    {
        return (string) config('laravel-web-analytics.table_name', 'lwa_logs');
    }

    public static function legacyReady(): bool
    {
        try {
            return Schema::hasTable(self::legacyTable());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function advancedReady(): bool
    {
        return empty(SchemaGuard::missing());
    }

    public static function anyReady(): bool
    {
        return self::legacyReady() || self::advancedReady();
    }

    /** @return string[] */
    public static function setupInstructions(): array
    {
        $lines = [];

        if (!self::legacyReady()) {
            $lines[] = 'Missing legacy table: ' . self::legacyTable();
        }
        if (!self::advancedReady()) {
            $lines[] = 'Missing advanced analytics tables: ' . implode(', ', SchemaGuard::missing());
        }

        $lines[] = 'Enable the schema if needed (WEB_ANALYTICS_SCHEMA_ENABLED=true), then run: php artisan migrate';
        $lines[] = sprintf(
            'Debug: env=%s, config=%s, config-cached=%s',
            var_export(filter_var(env('WEB_ANALYTICS_SCHEMA_ENABLED', false), FILTER_VALIDATE_BOOLEAN), true),
            var_export((bool) config('laravel-web-analytics.analytics.enabled', false), true),
            file_exists(base_path('bootstrap/cache/config.php')) ? 'yes' : 'no'
        );

        return $lines;
    }
}
