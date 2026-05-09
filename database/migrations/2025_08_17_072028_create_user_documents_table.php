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

            // Classification
            $table->string('type')->index(); // ex: PASSPORT, ID_CARD, DIPLOMA
            $table->string('document_number', 50)->nullable()->index();
            $table->text('description')->nullable();

            // --- FICHIERS (Recto, Verso, ou Multi-pages) ---
            // Format JSON : {"front": "path/id_front.jpg", "back": "path/id_back.jpg"}
            $table->json('files');

            // Validité
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->string('issuing_authority', 150)->nullable();

            // --- WORKFLOW ---
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'EXPIRED'])
                ->default('PENDING')
                ->index();

            $table->text('rejection_reason')->nullable(); // Pourquoi le document a été refusé

            // Validation Admin
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();

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
