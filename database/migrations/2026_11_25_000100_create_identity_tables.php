<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lwa_profiles')) {
            Schema::create('lwa_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->string('distinct_id', 100);
                $table->json('traits')->nullable();
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->unique(['website_id', 'distinct_id']);
                $table->index(['website_id', 'last_seen_at']);
            });
        }

        if (!Schema::hasTable('lwa_identity_aliases')) {
            Schema::create('lwa_identity_aliases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->string('anonymous_id', 100);
                $table->string('distinct_id', 100);
                $table->timestamp('aliased_at')->nullable();

                $table->unique(['website_id', 'anonymous_id']);
                $table->index(['website_id', 'distinct_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_identity_aliases');
        Schema::dropIfExists('lwa_profiles');
    }
};
