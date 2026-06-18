<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Type et Titre
            $table->enum('type', ['ACADEMIC', 'PROFESSIONAL', 'OTHER'])->default('ACADEMIC');
            $table->string('title'); // ex: "Stage de fin d'études - Développement Laravel"

            // Organisation
            $table->string('organization');
            $table->string('location')->nullable(); // ex: "Yaoundé, Cameroun"

            // Encadrement
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_contact')->nullable();

            // Période
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);

            // Contenu et Livrables
            $table->text('description')->nullable(); // Missions principales
            $table->text('technologies')->nullable(); // ex: "React, Tailwind, Docker"

            // Liaison vers le document (Attestation ou Rapport)
            $table->foreignId('certificate_document_id')
                ->nullable()
                ->constrained('user_documents')
                ->nullOnDelete();

            $table->enum('status', ['ONGOING', 'COMPLETED', 'CANCELED'])->default('ONGOING');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
