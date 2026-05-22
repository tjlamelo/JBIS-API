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
        Schema::create('required_documents', function (Blueprint $table) {
            $table->id();

            // On retire program_id et offer_id car la relation est maintenant dans les pivots
            // Données principales
            $table->json('name'); // ex: {"fr": "Passeport valide", "en": "Valid passport"}
            $table->string('slug')->unique(); // ex: "passeport-valide"

            // Type de fichier attendu
            $table->enum('type', ['PDF', 'IMAGE', 'WORD', 'OTHER'])->default('PDF')->index();

            $table->text('description')->nullable();

            // On garde template_path ici car le modèle de document (ex: un formulaire vide)
            // est lié au type de document lui-même, peu importe l'offre.
            $table->string('template_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_documents');
    }
};
