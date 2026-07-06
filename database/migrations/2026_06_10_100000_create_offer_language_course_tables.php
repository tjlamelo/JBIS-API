<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_language_course_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->foreignId('training_id')->nullable()->constrained('trainings')->nullOnDelete();
            $table->string('target_level', 16)->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['offer_id', 'language_id'], 'offer_lang_course_req_unique');
            $table->index(['offer_id', 'is_mandatory'], 'offer_lang_course_req_mandatory_idx');
        });

        Schema::create('candidate_language_courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->foreignId('training_id')->nullable()->constrained('trainings')->nullOnDelete();
            $table->foreignId('user_training_id')->nullable()->constrained('user_trainings')->nullOnDelete();
            $table->string('status', 32)->default('PLANNED')->index();
            $table->string('target_level', 16)->nullable();
            $table->string('achieved_level', 16)->nullable();
            $table->text('observations')->nullable();
            $table->text('staff_notes')->nullable();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'offer_id'], 'candidate_lang_courses_user_offer_idx');
            $table->index(['offer_id', 'status'], 'candidate_lang_courses_offer_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_language_courses');
        Schema::dropIfExists('offer_language_course_requirements');
    }
};
