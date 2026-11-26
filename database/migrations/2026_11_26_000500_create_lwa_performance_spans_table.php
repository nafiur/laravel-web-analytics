<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lwa_performance_spans')) {
            return;
        }

        Schema::create('lwa_performance_spans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->string('tenant_id', 100)->nullable()->index();
            $table->string('trace_id', 64)->index();
            $table->string('span_id', 64);
            $table->string('parent_id', 64)->nullable();
            $table->string('name', 150);
            $table->string('kind', 30)->default('internal');
            $table->unsignedBigInteger('start_ms')->default(0);
            $table->unsignedBigInteger('duration_ms')->default(0);
            $table->string('status', 20)->default('ok');
            $table->boolean('has_error')->default(false);
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->unique(['trace_id', 'span_id']);
            $table->index(['website_id', 'created_at']);
            $table->index(['name', 'duration_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_performance_spans');
    }
};
