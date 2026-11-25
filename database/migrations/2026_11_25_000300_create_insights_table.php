<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lwa_insights')) {
            return;
        }

        Schema::create('lwa_insights', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->json('config');
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->string('tenant_id', 100)->nullable()->index();
            $table->string('created_by', 64)->nullable();
            $table->timestamps();

            $table->index(['website_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_insights');
    }
};
