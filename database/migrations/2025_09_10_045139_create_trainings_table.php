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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();

            // Classification
            $table->string('domain')->index(); // ex: "Cloud Computing", "Management"
            $table->string('title');
            $table->string('organization'); // Ex: JBIS Training Center, Udemy, etc.
            $table->text('description')->nullable();

            // Période (Optionnel si c'est une formation à la demande / Self-paced)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

       
            $table->integer('duration_hours')->nullable();
            $table->integer('duration_days')->nullable();

            // Logistique & Accès
            $table->enum('mode', ['ONLINE', 'ONSITE', 'HYBRID'])->default('ONLINE');
            $table->string('location')->nullable(); // URL si ONLINE, Adresse si ONSITE
            $table->text('prerequisites')->nullable(); // Ce qu'il faut savoir avant

            // Certification
            $table->boolean('is_certified')->default(false); // Délivre un certificat ?
            $table->string('certificate_name')->nullable(); // Nom du titre obtenu

            // Statut pour le catalogue JBIS
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
