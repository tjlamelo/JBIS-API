<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_flows', function (Blueprint $table) {
            $table->id();
 
            // --- Versioning ---
            // Tous les flows du même groupe partagent le même flow_group_id (UUID).
            // La version courante est celle avec le status = 'published' et version MAX.
            // Ne jamais utiliser is_current : inférer via status + MAX(version).
            $table->uuid('flow_group_id')->index();
            $table->unsignedSmallInteger('version')->default(1);
 
            // draft     : en cours d'édition, non visible aux candidats
            // published : version active, une seule par flow_group_id
            // archived  : versions précédentes conservées pour historique
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
 
            // --- Identification (template abstrait, bilingue) ---
            $table->json('name');
            $table->json('description')->nullable();
 
            // --- Liaisons optionnelles (rattachement métier, pas définition du parcours) ---
            // Un même template peut exister sans programme, offre ni pays.
            // Ex. rattacher plus tard via l'admin ou lors de l'inscription candidat.
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
 
            // --- Metadata ---
            // Durée estimée totale de la procédure en jours
            $table->unsignedSmallInteger('estimated_duration_days')->nullable();
 
            // Somme des étapes PAYMENT hors FILE_OPENING (versements procédure)
            $table->decimal('total_procedure_fees', 15, 2)->unsigned()->default(0);

            // Somme des étapes PAYMENT type FILE_OPENING (hors total_procedure_fees)
            $table->decimal('file_opening_fee', 15, 2)->unsigned()->default(0);
 
            // Notes internes (non visibles candidats)
            $table->text('internal_notes')->nullable();
 
            $table->timestamps();
            $table->softDeletes();
 
            // Contraintes
            $table->unique(['flow_group_id', 'version']);
            $table->index(['country_id', 'status']);
        });

        Schema::create('process_flow_sections', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('process_flow_id')
                ->constrained('process_flows')
                ->cascadeOnDelete();
 
            // Clé technique stable pour référencer en code (ex: 'file_opening', 'interview')
            // Permet de retrouver une section par slug sans dépendre de l'ordre ou de l'ID
            $table->string('key', 64)->index();
 
            // Titre bilingue : {"fr": "Ouverture de dossier", "en": "File opening"}
            $table->json('title');
 
            // Description/introduction de la section (optionnelle)
            $table->json('description')->nullable();
 
            // Ordre d'affichage au sein du flow
            $table->unsignedSmallInteger('section_order')->default(1);
 
            // Couleur d'accentuation pour l'UI (optionnel, ex: '#E74C3C')
            $table->string('color', 16)->nullable();
 
            // Icône Tabler (ex: 'ti-file-text', 'ti-briefcase')
            $table->string('icon', 64)->nullable();
 
            // Section visible uniquement après validation d'une condition (ex: après l'entretien)
            // Référence la clé d'une section précédente
            $table->string('visible_after_section_key', 64)->nullable();
 
            $table->timestamps();
 
            $table->unique(['process_flow_id', 'key']);
            $table->index(['process_flow_id', 'section_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_flow_sections');
        Schema::dropIfExists('process_flows');
    }
};
