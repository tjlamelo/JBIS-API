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
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Traduisible
            $table->string('slug')->unique();
            $table->foreignId('skill_category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('offer_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();

            // Optionnel : niveau requis pour cette offre spécifique
            $table->string('level')->nullable(); // ex: 'Expert', 'Intermediate', 'Beginner'

            $table->timestamps();
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
