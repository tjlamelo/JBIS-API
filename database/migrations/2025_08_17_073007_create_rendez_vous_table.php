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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Relation optionnelle avec un utilisateur existant
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Relation obligatoire avec l'agence concernée
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();

            // Infos du demandeur (si non-connecté ou pour confirmation)
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();

            // Détails temporels
            $table->dateTime('scheduled_at')->index();
            $table->integer('duration_minutes')->default(30); // Pour bloquer le calendrier

            // Détails logistiques
            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->foreignId('discovery_source_id')
                ->nullable()
                ->constrained('discovery_sources')
                ->nullOnDelete();
            $table->string('discovery_source_other', 255)->nullable();
            $table->string('utm_source', 128)->nullable();
            $table->string('utm_medium', 128)->nullable();
            $table->string('utm_campaign', 128)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->enum('type', ['IN_PERSON', 'ONLINE', 'PHONE'])->default('IN_PERSON');
            $table->string('meeting_link')->nullable(); // Si ONLINE (Meet, Zoom)

            // Statut (en majuscules pour la cohérence de ton projet)
            $table->enum('status', ['PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED', 'NOSHOW'])
                ->default('PENDING')
                ->index();

            // Notes internes (pour le manager d'agence)
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index composé pour éviter les collisions de planning par agence
            $table->unique(['agency_id', 'scheduled_at'], 'unique_appointment_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
