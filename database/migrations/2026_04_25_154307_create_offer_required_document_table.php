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
      Schema::create('offer_required_document', function (Blueprint $table) {
    $table->id();

    // Clés étrangères
    $table->foreignId('offer_id')
          ->constrained()
          ->onDelete('cascade');

    $table->foreignId('required_document_id')
          ->constrained('required_documents')
          ->onDelete('cascade');

    // Attributs spécifiques à la relation
    $table->boolean('is_mandatory')->default(true);
    $table->integer('sort_order')->default(0);

    // Index de sécurité pour éviter les doublons (une offre ne peut pas demander 2 fois le même doc)
    $table->unique(['offer_id', 'required_document_id'], 'offer_doc_unique');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_required_document');
    }
};
