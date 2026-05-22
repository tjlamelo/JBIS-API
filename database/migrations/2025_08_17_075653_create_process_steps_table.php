<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
 
            // --- Relations ---
            $table->foreignId('process_flow_id')
                ->constrained('process_flows')
                ->cascadeOnDelete();
 
            // Section parente (nullable pour migration backward-compat)
            $table->foreignId('process_flow_section_id')
                ->nullable()
                ->constrained('process_flow_sections')
                ->nullOnDelete();
 
            // --- Typage ---
            $table->enum('step_type', [
                'DOCUMENT_COLLECTION',
                'PAYMENT',
                'SERVICE',
                'INTERVIEW',
                'SIGNING',
                'ADMINISTRATIVE',
                'IMMIGRATION_EXIT',
                'INFO',
            ])->default('INFO')->index();
 
            // Sous-type paiement (null si step_type != PAYMENT)
            $table->enum('payment_type', [
                'FILE_OPENING',
                'PROCEDURE_INSTALMENT',
                'BLOCKED_ACCOUNT',
            ])->nullable();
 
            // Qui réalise cette étape
            $table->enum('responsible_party', [
                'CANDIDATE',
                'JBIS',
                'EMPLOYER',
                'AUTHORITY',
                'SHARED',
            ])->default('CANDIDATE');
 
            // --- Contenu bilingue ---
            // {"fr": "Ouverture du dossier", "en": "File opening"}
            $table->json('title');
 
            // Description détaillée, peut contenir des instructions longues
            // {"fr": "Le candidat doit déposer...", "en": "The candidate must submit..."}
            $table->json('description')->nullable();
 
            // Note interne JBIS (non visible candidat)
            $table->text('internal_note')->nullable();
 
            // --- Ordre & flux ---
            $table->unsignedSmallInteger('step_order')->default(1);
 
            // Étape bloquante : la suivante ne peut démarrer avant validation de celle-ci
            $table->boolean('is_blocking')->default(true);
 
            // Étape obligatoire (vs optionnelle selon le profil candidat)
            $table->boolean('is_required')->default(true);
 
            // --- Paiement ---
            $table->decimal('default_amount', 15, 2)->unsigned()->default(0);
 
            // Banques acceptées pour ce paiement (ex: ["ORIS_FINANCE", "SCB"])
            $table->json('accepted_banks')->nullable();
 
            // --- Documents ---
            // Cette étape nécessite-t-elle des documents du candidat ?
            $table->boolean('requires_documents')->default(false);
 
            // FK logiques vers document_types (catalogue pièces)
            $table->json('document_type_ids')->nullable();

            // --- Timing ---
            // Durée estimée de cette étape en jours
            $table->unsignedSmallInteger('estimated_duration_days')->nullable();
 
            // Délai maximum avant alerte (SLA interne JBIS)
            $table->unsignedSmallInteger('sla_alert_days')->nullable();
 
            $table->timestamps();
 
            $table->index(['process_flow_id', 'step_order']);
            $table->index(['process_flow_id', 'process_flow_section_id']);
            $table->index(['step_type', 'responsible_party']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
