<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['lwa_pageviews', 'lwa_events'] as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'request_uuid')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->string('request_uuid', 64)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        foreach (['lwa_pageviews', 'lwa_events'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'request_uuid')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('request_uuid');
            });
        }
    }
};
