<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lwa_error_groups')) {
            Schema::create('lwa_error_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('fingerprint', 64);
                $table->string('type', 150);
                $table->text('message');
                $table->string('file', 255)->nullable();
                $table->unsignedInteger('line')->nullable();
                $table->string('severity', 20)->default('error');
                $table->unsignedBigInteger('occurrences')->default(0);
                $table->string('status', 20)->default('open');
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['website_id', 'tenant_id', 'fingerprint']);
                $table->index(['website_id', 'status', 'last_seen_at']);
            });
        }

        if (! Schema::hasTable('lwa_error_events')) {
            Schema::create('lwa_error_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('error_group_id')->index();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('event_uuid', 64)->unique();
                $table->timestamp('occurred_at')->nullable()->index();
                $table->string('severity', 20)->default('error');
                $table->json('context')->nullable();
                $table->text('stack')->nullable();
                $table->timestamps();

                $table->index(['error_group_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_error_events');
        Schema::dropIfExists('lwa_error_groups');
    }
};
