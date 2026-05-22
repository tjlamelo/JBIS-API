<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Traduisible
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Traduisible
            $table->string('slug')->unique();
            $table->foreignId('skill_category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->integer('years_of_experience')->nullable();
            $table->enum('level', ['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'EXPERT'])->default('BEGINNER');
            $table->timestamps();

            $table->softDeletes();
        });
        Schema::create('offer_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();

            // Optionnel : niveau requis pour cette offre spécifique
            $table->string('level')->nullable(); // ex: 'Expert', 'Intermediate', 'Beginner'

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_skill');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
    }
};
