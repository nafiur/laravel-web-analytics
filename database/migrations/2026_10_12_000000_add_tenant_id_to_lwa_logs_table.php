<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');

        if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('tenant_id', 100)->nullable()->index()->after('session_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
