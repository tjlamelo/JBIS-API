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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();

            // --- IDENTITÉ & SEO ---
            $table->json('name'); // Traduisible : {"fr": "Agence Bastos", "en": "Bastos Branch"}
            $table->string('slug')->unique()->index();
            $table->json('description')->nullable();

            // --- LOCALISATION NORMALISÉE ---
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // --- CONTACTS MULTIPLES (JSON) ---
            $table->json('phones')->nullable();           // ex: ["+237...", "+237..."]
            $table->json('whatsapp_numbers')->nullable();  // ex: ["+237...", "+237..."]
            $table->string('email')->unique();

            // --- MANAGEMENT (FK) ---
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // --- CONFIGURATION & STAFF ---
            $table->unsignedInteger('number_of_employees')->default(0);
            $table->json('opening_hours')->nullable(); // ex: {"mon": "08:00-17:00", ...}
            $table->string('image_url')->nullable();

            // --- STATUT & SYSTÈME ---
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
