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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            // Type et Lieu
            $table->enum('type', ['ONLINE', 'ONSITE', 'HYBRID'])->default('ONSITE');
            $table->string('title'); // ex: "Briefing Hebdomadaire JBIS"
            $table->string('location')->nullable(); // Salle de conférence ou Lien (Meet/Zoom)

            // Chronologie
            $table->dateTime('scheduled_at'); // Date et heure prévues
            $table->integer('duration_minutes')->nullable(); // Durée réelle

            // Contenu
            $table->text('agenda')->nullable(); // Ordre du jour (ce qui était prévu)
            $table->text('minutes')->nullable(); // Compte-rendu / Procès-verbal (ce qui a été dit)
            $table->text('decisions')->nullable(); // Résumé des décisions clés

            // Organisation
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();

            // Statut
            $table->enum('status', ['SCHEDULED', 'ONGOING', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED');

            $table->timestamps();
        });
        Schema::create('meeting_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_present')->default(false);
            $table->string('excuse_reason')->nullable(); // Si absent, pourquoi ?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
