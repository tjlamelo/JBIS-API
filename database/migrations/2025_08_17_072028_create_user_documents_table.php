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
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->string('document_number', 50)->nullable()->index();
            $table->foreignId('issuing_country_id')->nullable()->constrained('countries');

            // Métadonnées du fichier
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // En octets

            // Validité
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->boolean('is_expired')->virtualAs('expiry_date < CURDATE()'); // Colonne calculée (si MariaDB 10.2+)

            // Workflow & Validation
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'EXPIRED'])->default('PENDING')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            // Sécurité
            $table->boolean('is_verified_copy')->default(false); // Si l'agent a vu l'original
            $table->boolean('is_sensitive')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
