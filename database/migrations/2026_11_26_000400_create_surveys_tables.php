<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lwa_surveys')) {
            Schema::create('lwa_surveys', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('key', 100);
                $table->string('name', 150);
                $table->string('type', 20)->default('nps'); // nps | text
                $table->text('question');
                $table->string('target_path', 500)->nullable();
                $table->string('status', 20)->default('draft'); // draft | running | stopped
                $table->string('created_by', 64)->nullable();
                $table->timestamps();

                $table->index(['website_id', 'tenant_id', 'key']);
            });
        }

        if (! Schema::hasTable('lwa_survey_responses')) {
            Schema::create('lwa_survey_responses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('survey_id')->index();
                $table->unsignedBigInteger('website_id')->nullable()->index();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('subject_hash', 64)->nullable()->index();
                $table->string('path', 500)->nullable();
                $table->unsignedTinyInteger('score')->nullable();
                $table->text('answer')->nullable();
                $table->timestamps();

                $table->index(['survey_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lwa_survey_responses');
        Schema::dropIfExists('lwa_surveys');
    }
};
