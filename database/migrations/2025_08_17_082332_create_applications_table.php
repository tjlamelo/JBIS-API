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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique(); // ex: JBIS-2026-X
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // On garde une trace du flow d'origine, mais on travaillera sur les copies
            $table->foreignId('process_flow_id')->constrained();

            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->integer('current_step_order')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('application_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // Copie conforme de l'étape au moment de l'inscription
            $table->integer('step_order');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount_to_pay', 15, 2)->default(0);

            $table->foreignId('protocol_document_id')
                    ->nullable()
                    ->constrained('user_documents')
                    ->nullOnDelete();

            $table->boolean('has_accepted_flow')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->string('ip_address')->nullable(); // Preuve numérique de l'acceptation

            // Consentement physique (après impression et signature)
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_contract_id')
                ->nullable()
                ->constrained('user_documents')
                ->nullOnDelete();

            // Suivi de l'exécution
            $table->enum('status', ['LOCKED', 'PENDING', 'COMPLETED', 'SKIPPED'])->default('LOCKED');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
