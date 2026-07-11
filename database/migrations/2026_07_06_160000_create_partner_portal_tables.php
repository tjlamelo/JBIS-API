<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('partner_organizations')) {
            Schema::create('partner_organizations', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('status', 32)->default('pending');
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->foreign('company_id', 'po_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('partner_organization_user')) {
            Schema::create('partner_organization_user', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_organization_id');
                $table->unsignedBigInteger('user_id');
                $table->boolean('is_owner')->default(false);
                $table->timestamps();

                $table->unique(['partner_organization_id', 'user_id'], 'pou_org_user_unique');
                $table->foreign('partner_organization_id', 'pou_org_id_foreign')
                    ->references('id')
                    ->on('partner_organizations')
                    ->cascadeOnDelete();
                $table->foreign('user_id', 'pou_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('partner_cohorts')) {
            Schema::create('partner_cohorts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_organization_id');
                $table->string('name');
                $table->string('academic_year', 32)->nullable();
                $table->string('field_of_study')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status', 32)->default('draft');
                $table->unsignedSmallInteger('expected_student_count')->default(0);
                $table->text('description')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->unsignedBigInteger('submitted_by_user_id')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('staff_note')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->index(['partner_organization_id', 'status'], 'pc_org_status_idx');
                $table->foreign('partner_organization_id', 'pc_org_id_foreign')
                    ->references('id')
                    ->on('partner_organizations')
                    ->cascadeOnDelete();
                $table->foreign('submitted_by_user_id', 'pc_submitted_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
                $table->foreign('reviewed_by', 'pc_reviewed_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('partner_cohort_required_documents')) {
            Schema::create('partner_cohort_required_documents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_cohort_id');
                $table->string('document_type_code', 64);
                $table->boolean('is_mandatory')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->json('label_override')->nullable();
                $table->timestamps();

                $table->unique(['partner_cohort_id', 'document_type_code'], 'pcrd_cohort_code_unique');
                $table->foreign('partner_cohort_id', 'pcrd_cohort_id_foreign')
                    ->references('id')
                    ->on('partner_cohorts')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('partner_cohort_students')) {
            Schema::create('partner_cohort_students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_cohort_id');
                $table->unsignedBigInteger('student_user_id')->nullable();
                $table->string('invited_email')->nullable();
                $table->string('invited_name')->nullable();
                $table->string('enrollment_status', 32)->default('invited');
                $table->string('placement_status', 32)->default('pending');
                $table->unsignedBigInteger('user_internship_id')->nullable();
                $table->text('partner_notes')->nullable();
                $table->text('staff_notes')->nullable();
                $table->timestamp('enrolled_at')->nullable();
                $table->unsignedBigInteger('enrolled_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['partner_cohort_id', 'enrollment_status'], 'pcs_cohort_enroll_idx');
                $table->foreign('partner_cohort_id', 'pcs_cohort_id_foreign')
                    ->references('id')
                    ->on('partner_cohorts')
                    ->cascadeOnDelete();
                $table->foreign('student_user_id', 'pcs_student_user_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
                $table->foreign('user_internship_id', 'pcs_internship_foreign')
                    ->references('id')
                    ->on('internships')
                    ->nullOnDelete();
                $table->foreign('enrolled_by_user_id', 'pcs_enrolled_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('partner_cohort_student_documents')) {
            Schema::create('partner_cohort_student_documents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_cohort_student_id');
                $table->string('document_type_code', 64);
                $table->unsignedBigInteger('user_document_id')->nullable();
                $table->string('status', 32)->default('missing');
                $table->unsignedBigInteger('validated_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->text('staff_note')->nullable();
                $table->timestamps();

                $table->unique(['partner_cohort_student_id', 'document_type_code'], 'pcsd_student_code_unique');
                $table->foreign('partner_cohort_student_id', 'pcsd_student_id_foreign')
                    ->references('id')
                    ->on('partner_cohort_students')
                    ->cascadeOnDelete();
                $table->foreign('user_document_id', 'pcsd_user_doc_foreign')
                    ->references('id')
                    ->on('user_documents')
                    ->nullOnDelete();
                $table->foreign('validated_by', 'pcsd_validated_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_cohort_student_documents');
        Schema::dropIfExists('partner_cohort_students');
        Schema::dropIfExists('partner_cohort_required_documents');
        Schema::dropIfExists('partner_cohorts');
        Schema::dropIfExists('partner_organization_user');
        Schema::dropIfExists('partner_organizations');
    }
};
