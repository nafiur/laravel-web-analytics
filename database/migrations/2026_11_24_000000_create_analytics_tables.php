<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * advanced analytics data model — opt-in through WEB_ANALYTICS_SCHEMA_ENABLED.
 *
 * The legacy lwa_logs table is left untouched; these tables are additive so
 * existing v2 installations keep working until the upgrade command is run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!filter_var(env('WEB_ANALYTICS_SCHEMA_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if (!Schema::hasTable('lwa_websites')) {
            Schema::create('lwa_websites', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('name');
                $table->string('domain')->index();
                $table->string('tracking_id')->unique();
                $table->unsignedInteger('retention_days')->nullable();
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lwa_visitors')) {
            Schema::create('lwa_visitors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->index();
                $table->string('visitor_hash', 64);
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable()->index();
                $table->string('country_code', 2)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('device', 50)->nullable();
                $table->string('browser', 50)->nullable();
                $table->string('os', 50)->nullable();
                $table->timestamps();

                $table->unique(['website_id', 'visitor_hash']);
            });
        }

        if (!Schema::hasTable('lwa_sessions')) {
            Schema::create('lwa_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->index();
                $table->unsignedBigInteger('visitor_id')->index();
                $table->string('session_key', 100)->unique();
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->text('landing_path')->nullable();
                $table->text('exit_path')->nullable();
                $table->unsignedInteger('pageviews_count')->default(0);
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->boolean('is_bounce')->default(false)->index();
                $table->text('referrer')->nullable();
                $table->string('utm_source', 100)->nullable();
                $table->string('utm_medium', 100)->nullable();
                $table->string('utm_campaign', 100)->nullable();
                $table->timestamps();

                $table->index(['website_id', 'started_at']);
            });
        }

        if (!Schema::hasTable('lwa_pageviews')) {
            Schema::create('lwa_pageviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->index();
                $table->unsignedBigInteger('session_id')->index();
                $table->unsignedBigInteger('visitor_id')->index();
                $table->text('path');
                $table->string('title', 255)->nullable();
                $table->unsignedInteger('status_code')->default(200);
                $table->decimal('response_time_ms', 10, 2)->default(0);
                $table->unsignedBigInteger('bytes_sent')->default(0);
                $table->timestamp('created_at')->index();

                $table->index(['website_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('lwa_events')) {
            Schema::create('lwa_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->index();
                $table->unsignedBigInteger('session_id')->nullable()->index();
                $table->unsignedBigInteger('visitor_id')->nullable()->index();
                $table->string('name', 100);
                $table->json('properties')->nullable();
                $table->decimal('value', 12, 2)->nullable();
                $table->timestamp('created_at')->index();

                $table->index(['website_id', 'name', 'created_at']);
            });
        }

        foreach (['lwa_hourly_stats' => 'hourly', 'lwa_daily_stats' => 'daily'] as $tableName => $granularity) {
            if (Schema::hasTable($tableName)) {
                continue;
            }

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->timestamp('bucket_at');
                $table->unsignedInteger('visitors')->default(0);
                $table->unsignedInteger('sessions')->default(0);
                $table->unsignedInteger('pageviews')->default(0);
                $table->unsignedInteger('bounces')->default(0);
                $table->unsignedInteger('engagement_seconds')->default(0);
                $table->unsignedBigInteger('bytes')->default(0);
                $table->unsignedInteger('errors_4xx')->default(0);
                $table->unsignedInteger('errors_5xx')->default(0);
                $table->decimal('avg_response_ms', 10, 2)->default(0);

                $table->unique(['website_id', 'bucket_at']);
            });
        }

        if (!Schema::hasTable('lwa_upgrade_checkpoints')) {
            Schema::create('lwa_upgrade_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedBigInteger('last_id')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (!filter_var(env('WEB_ANALYTICS_SCHEMA_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        foreach ([
            'lwa_upgrade_checkpoints', 'lwa_daily_stats', 'lwa_hourly_stats',
            'lwa_events', 'lwa_pageviews', 'lwa_sessions', 'lwa_visitors', 'lwa_websites',
        ] as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
