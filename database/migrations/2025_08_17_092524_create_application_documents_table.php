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
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_document_id')->constrained()->cascadeOnDelete();

            // OPTIONNEL : Pour savoir à quelle étape du flow ce document a été exigé
            $table->foreignId('application_step_id')->nullable()->constrained()->nullOnDelete();

            // Statut de la revue (JBIS)
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'REVISION_REQUIRED'])->default('PENDING');
            $table->text('admin_notes')->nullable(); // Ex: "Traduction manquante"
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['application_id', 'user_document_id']);
            $table->index(['application_step_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
