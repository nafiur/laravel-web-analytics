<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lwa_dead_letters')) {
            return;
        }

        Schema::create('lwa_dead_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->nullable();
            $table->json('payload');
            $table->text('error')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['website_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_dead_letters');
    }
};
