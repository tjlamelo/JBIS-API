<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_onboarding_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('legal_form')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->text('activity_description')->nullable();
            $table->string('desired_slug', 80)->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('staff_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('recruiter_organization_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'submitted_at'], 'rec_onboarding_status_submitted_idx');
            $table->foreign('recruiter_organization_id', 'rec_onboard_org_fk')
                ->references('id')->on('recruiter_organizations')->nullOnDelete();
        });

        Schema::create('recruiter_onboarding_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recruiter_onboarding_application_id');
            $table->string('document_type', 64);
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 127)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->foreign('recruiter_onboarding_application_id', 'rec_onboard_doc_app_fk')
                ->references('id')->on('recruiter_onboarding_applications')->cascadeOnDelete();
        });

        Schema::create('recruiter_offer_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->string('status', 32)->default('draft')->index();
            $table->json('payload');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('staff_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['recruiter_organization_id', 'status'], 'rec_offer_sub_org_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_offer_submissions');
        Schema::dropIfExists('recruiter_onboarding_documents');
        Schema::dropIfExists('recruiter_onboarding_applications');
    }
};
