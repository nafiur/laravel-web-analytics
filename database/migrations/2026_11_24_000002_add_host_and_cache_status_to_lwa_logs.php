<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = Schema::getColumnListing($tableName);

        Schema::table($tableName, function (Blueprint $table) use ($existing) {
            if (! in_array('host', $existing, true)) {
                $table->string('host', 255)->nullable()->index()->after('full_url');
            }
            if (! in_array('cache_status', $existing, true)) {
                $table->string('cache_status', 20)->nullable()->index()->after('status_code');
            }
        });

        // Backfill hosts from already-recorded full URLs. Grouping ids by host
        // keeps this to a handful of bulk updates even on large tables.
        if (in_array('full_url', $existing, true)) {
            DB::table($tableName)
                ->select(['id', 'full_url'])
                ->whereNull('host')
                ->whereNotNull('full_url')
                ->chunkById(1000, function ($rows) use ($tableName) {
                    $groups = [];
                    foreach ($rows as $row) {
                        $host = parse_url((string) $row->full_url, PHP_URL_HOST);
                        if (! is_string($host) || $host === '') {
                            continue;
                        }
                        $groups[substr($host, 0, 255)][] = $row->id;
                    }
                    foreach ($groups as $host => $ids) {
                        DB::table($tableName)->whereIn('id', $ids)->update(['host' => $host]);
                    }
                });
        }
    }

    public function down(): void
    {
        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = Schema::getColumnListing($tableName);
        $columns = array_values(array_intersect(['host', 'cache_status'], $existing));

        if (! empty($columns)) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
