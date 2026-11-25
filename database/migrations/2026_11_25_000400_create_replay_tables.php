<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replay index tables. Chunk payloads live on a Laravel disk; the
     * relational side only records where they are and what they contain.
     */
    public function up(): void
    {
        if (! Schema::hasTable('lwa_replay_sessions')) {
            Schema::create('lwa_replay_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_key', 64)->unique();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('user_id', 100)->nullable()->index();
                $table->string('url', 500)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->unsignedInteger('duration_ms')->default(0);
                $table->unsignedInteger('event_count')->default(0);
                $table->unsignedBigInteger('bytes')->default(0);
                $table->decimal('sample_rate', 5, 4)->default(0);
                $table->timestamps();

                $table->index(['website_id', 'started_at']);
                $table->index(['website_id', 'tenant_id']);
            });
        }

        if (! Schema::hasTable('lwa_replay_chunks')) {
            Schema::create('lwa_replay_chunks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('replay_session_id')->index();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->unsignedInteger('sequence');
                $table->string('disk_path', 255);
                $table->string('encoding', 10)->default('gzip');
                $table->unsignedBigInteger('bytes')->default(0);
                $table->unsignedInteger('event_count')->default(0);
                $table->unsignedInteger('from_ms')->default(0);
                $table->unsignedInteger('to_ms')->default(0);
                $table->timestamps();

                $table->unique(['replay_session_id', 'sequence']);
                $table->index(['website_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('lwa_replay_markers')) {
            Schema::create('lwa_replay_markers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('replay_session_id')->index();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('type', 50);
                $table->unsignedInteger('at_ms')->default(0);
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->index(['replay_session_id', 'at_ms']);
            });
        }

        if (! Schema::hasTable('lwa_replay_assets')) {
            Schema::create('lwa_replay_assets', function (Blueprint $table) {
                $table->id();
                $table->string('content_hash', 64)->unique();
                $table->string('disk_path', 255);
                $table->string('mime', 100)->default('text/css');
                $table->unsignedBigInteger('bytes')->default(0);
                $table->unsignedInteger('ref_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_replay_markers');
        Schema::dropIfExists('lwa_replay_chunks');
        Schema::dropIfExists('lwa_replay_sessions');
        Schema::dropIfExists('lwa_replay_assets');
    }
};
