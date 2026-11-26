<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lwa_replay_chunks') && ! Schema::hasColumn('lwa_replay_chunks', 'format')) {
            Schema::table('lwa_replay_chunks', function (Blueprint $table) {
                $table->string('format', 20)->default('snapshot')->after('encoding');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lwa_replay_chunks') && Schema::hasColumn('lwa_replay_chunks', 'format')) {
            Schema::table('lwa_replay_chunks', function (Blueprint $table) {
                $table->dropColumn('format');
            });
        }
    }
};
