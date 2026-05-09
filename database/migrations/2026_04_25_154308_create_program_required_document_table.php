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
        Schema::create('program_required_document', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('program_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('required_document_id')
                ->constrained('required_documents') // Précise le nom de la table cible
                ->onDelete('cascade');

            // Attributs spécifiques à la relation
            $table->boolean('is_mandatory')->default(true);
            $table->integer('sort_order')->default(0); // Pour gérer l'ordre d'affichage dans ton interface

            // Indexation pour les performances de recherche
            $table->unique(['program_id', 'required_document_id'], 'program_doc_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_required_document');
    }
};
