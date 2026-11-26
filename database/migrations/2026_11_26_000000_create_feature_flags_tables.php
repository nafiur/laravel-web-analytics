<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lwa_feature_flags')) {
            Schema::create('lwa_feature_flags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('key', 100);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('type', 20)->default('boolean'); // boolean | multivariate
                $table->json('variants')->nullable();
                $table->boolean('enabled')->default(false);
                $table->unsignedTinyInteger('rollout')->default(0); // 0–100
                $table->json('targeting')->nullable();
                $table->string('created_by', 64)->nullable();
                $table->timestamps();

                $table->index(['website_id', 'tenant_id', 'key']);
            });
        }

        if (! Schema::hasTable('lwa_flag_overrides')) {
            Schema::create('lwa_flag_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flag_id')->index();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('subject_key', 150);
                $table->string('variant', 100);
                $table->timestamps();

                $table->unique(['flag_id', 'subject_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_flag_overrides');
        Schema::dropIfExists('lwa_feature_flags');
    }
};
