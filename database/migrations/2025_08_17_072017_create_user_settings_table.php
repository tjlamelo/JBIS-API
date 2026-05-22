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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            // Relation 1-to-1 unique
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Préférences de l'interface
            $table->string('language', 5)->default('fr')->index(); // 'fr', 'en'
            $table->string('theme', 20)->default('light'); // 'light', 'dark', 'system'
            $table->string('timezone')->default('Africa/Douala'); // Crucial pour JBIS

            // Paramètres complexes (Stockés en JSON)
            $table->json('notifications')->nullable(); // {email: true, sms: false, push: true}

            $table->json('marketing')->nullable();     // {newsletter: true, partner_offers: false}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
