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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Informations du fichier
            $table->string('original_name');      // ex: "devis_travaux_final.pdf"
            $table->string('stored_name');        // ex: "archives/2026/05/abc123xyz.pdf"

            // Métadonnées pour la gestion universelle
            $table->string('file_type', 50);      // ex: pdf, image, video, archive, document
            $table->string('extension', 10);      // ex: pdf, jpg, zip, docx
            $table->string('mime_type');          // ex: application/pdf, image/jpeg
            $table->unsignedBigInteger('size');   // Taille en octets (Bytes)

            // Organisation
            $table->string('category')->nullable()->index(); // ex: LOGS, RH, ADMIN
            $table->text('description')->nullable();

            // Configuration de stockage
            $table->string('disk')->default('local');
            $table->boolean('is_public')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
