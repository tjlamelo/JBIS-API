<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 32)->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Contexte d'inscription (une offre OU un programme par dossier)
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();

            // Version figée du parcours utilisée à la création du dossier
            $table->foreignId('process_flow_id')->constrained();
            $table->uuid('flow_group_id')->index();
            $table->unsignedSmallInteger('process_flow_version');

            // Protocole / conditions — spécifique à ce dossier (offre ou programme)
            $table->foreignId('protocol_document_id')
                ->nullable()
                ->constrained('user_documents')
                ->nullOnDelete();
            $table->boolean('has_accepted_protocol')->default(false);
            $table->timestamp('protocol_accepted_at')->nullable();
            $table->string('protocol_acceptance_ip', 45)->nullable();

            $table->enum('status', [
                'PENDING',
                'IN_PROGRESS',
                'APPROVED',
                'REJECTED',
                'CANCELLED',
            ])->default('PENDING')->index();
            $table->boolean('is_private')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Pointeur rapide vers l'étape courante (FK ajoutée après création de application_steps)
            $table->unsignedBigInteger('current_application_step_id')->nullable()->index();

            // Totaux dénormalisés (recalculés à chaque paiement — lecture dashboard)
            $table->decimal('total_due', 15, 2)->unsigned()->default(0);
            $table->decimal('total_paid', 15, 2)->unsigned()->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'offer_id']);
            $table->index(['user_id', 'program_id']);
            $table->index('process_flow_id');
        });

        Schema::create('application_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // Référence template (audit ; le snapshot reste source de vérité métier)
            $table->foreignId('process_step_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedSmallInteger('step_order');
            $table->string('section_key', 64)->nullable()->index();

            $table->enum('step_type', [
                'DOCUMENT_COLLECTION',
                'PAYMENT',
                'SERVICE',
                'INTERVIEW',
                'SIGNING',
                'ADMINISTRATIVE',
                'IMMIGRATION_EXIT',
                'INFO',
            ])->default('INFO')->index();

            $table->enum('payment_type', [
                'FILE_OPENING',
                'PROCEDURE_INSTALMENT',
                'BLOCKED_ACCOUNT',
            ])->nullable();

            $table->enum('responsible_party', [
                'CANDIDATE',
                'JBIS',
                'EMPLOYER',
                'AUTHORITY',
                'SHARED',
            ])->default('CANDIDATE');

            // Snapshot bilingue au moment de l'inscription
            $table->json('title');
            $table->json('description')->nullable();

            $table->boolean('is_blocking')->default(true);
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_documents')->default(false);
            $table->json('document_type_ids')->nullable();

            // Montants figés pour cette étape
            $table->decimal('amount_due', 15, 2)->unsigned()->default(0);
            $table->decimal('amount_paid', 15, 2)->unsigned()->default(0);
            $table->enum('payment_status', [
                'UNPAID',
                'PARTIAL',
                'PAID',
                'OVERPAID',
                'WAIVED',
            ])->default('UNPAID')->index();

            // Validations staff (oui/non par type d'étape)
            $table->boolean('documents_validated')->default(false);
            $table->timestamp('documents_validated_at')->nullable();
            $table->foreignId('documents_validated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('interview_passed')->nullable();
            $table->timestamp('interview_validated_at')->nullable();
            $table->foreignId('interview_validated_by')->nullable()->constrained('users')->nullOnDelete();

            // Signature (étapes SIGNING uniquement)
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_contract_id')
                ->nullable()
                ->constrained('user_documents')
                ->nullOnDelete();

            $table->enum('status', ['LOCKED', 'PENDING', 'COMPLETED', 'SKIPPED'])->default('LOCKED')->index();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['application_id', 'step_order']);
            $table->index(['application_id', 'status']);
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->foreign('current_application_step_id')
                ->references('id')
                ->on('application_steps')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropForeign(['current_application_step_id']);
        });
        Schema::dropIfExists('application_steps');
        Schema::dropIfExists('applications');
    }
};
