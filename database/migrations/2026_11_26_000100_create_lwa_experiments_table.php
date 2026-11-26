<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lwa_experiments')) {
            return;
        }

        Schema::create('lwa_experiments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->string('tenant_id', 100)->nullable()->index();
            $table->string('flag_key', 100);
            $table->string('name', 150);
            $table->string('goal_event', 100);
            $table->string('variant_property', 100)->default('variant');
            $table->string('control_variant', 100)->default('control');
            $table->string('status', 20)->default('draft'); // draft | running | stopped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('created_by', 64)->nullable();
            $table->timestamps();

            $table->index(['website_id', 'tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_experiments');
    }
};
