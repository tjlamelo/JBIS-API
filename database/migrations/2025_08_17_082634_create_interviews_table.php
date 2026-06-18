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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

            // Planification
            $table->dateTime('scheduled_date')->nullable();
            $table->integer('duration')->nullable(); // en minutes
            $table->enum('interview_type', ['ONLINE', 'PHONE', 'ONSITE'])->default('ONLINE');
            $table->string('location')->nullable(); // Lien Meet/Zoom ou adresse physique
            $table->string('interviewer_name')->nullable();

            // Résultats et Feedback
            $table->enum('status', ['SCHEDULED', 'COMPLETED', 'CANCELLED', 'RESCHEDULED', 'NO_SHOW'])->default('SCHEDULED');
            $table->enum('result', ['PASSED', 'FAILED', 'PENDING', 'WAITING_LIST'])->default('PENDING');

            // Le "Rapport" proprement dit
            $table->text('internal_notes')->nullable(); // Notes de JBIS
            $table->text('candidate_feedback')->nullable(); // Ce que le candidat a ressenti
            $table->json('evaluation_criteria')->nullable(); // Score par compétence (ex: Anglais: 4/5)

            // Négociation & Spécifications (Étape 3 du Flow)
            $table->decimal('salary_offered', 15, 2)->nullable();
            $table->decimal('salary_negotiated', 15, 2)->nullable();
            $table->text('work_conditions_notes')->nullable(); // Ex: Logement fourni, transport, etc.

            // Liaison vers un document de rapport (via user_documents)
            $table->foreignId('report_document_id')->nullable()->constrained('user_documents')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
