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

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->nullable()->index();
                $table->string('session_id', 100)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('method', 10)->default('GET');
                $table->text('path')->nullable();
                $table->text('full_url')->nullable();
                $table->integer('status_code')->default(200)->index();
                $table->decimal('response_time_ms', 10, 2)->default(0);
                $table->unsignedBigInteger('bytes_sent')->default(0);
                $table->string('country', 100)->nullable()->index();
                $table->string('city', 100)->nullable();
                $table->string('device', 50)->nullable()->index();
                $table->string('os', 50)->nullable()->index();
                $table->string('browser', 50)->nullable()->index();
                $table->text('user_agent')->nullable();
                $table->text('referrer')->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');
        Schema::dropIfExists($tableName);
    }
};
