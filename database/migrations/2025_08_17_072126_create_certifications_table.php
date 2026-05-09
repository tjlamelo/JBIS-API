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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Détails de la certification
            $table->string('name')->index(); // certification_name -> name
            $table->string('issuing_organization')->index();

            // --- RELATION DOCUMENT (FK) ---
            $table->foreignId('document_id')->nullable()->constrained('user_documents')->nullOnDelete();

            // Dates & Validité
            $table->date('issue_date');
            $table->date('expiry_date')->nullable()->index();

            $table->string('credential_id')->nullable(); // Numéro de licence/certificat
            $table->string('credential_url')->nullable(); // Lien de vérification en ligne

            // Workflow de validation
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
