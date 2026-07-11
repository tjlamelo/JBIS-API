<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->string('portal_host')->nullable();
            $table->string('api_host')->nullable();
            $table->string('mailbox_email')->nullable();
            $table->text('provisioning_error')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('recruiter_organization_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_owner')->default(false);
            $table->timestamps();

            $table->unique(['recruiter_organization_id', 'user_id'], 'rec_org_user_unique');
        });

        Schema::create('recruiter_profile_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_organization_id')->constrained('recruiter_organizations')->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('draft');
            $table->string('title');
            $table->json('criteria');
            $table->unsignedSmallInteger('quantity_needed')->default(10);
            $table->text('note')->nullable();
            $table->json('matched_candidate_ids')->nullable();
            $table->unsignedSmallInteger('matched_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('staff_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('transmitted_at')->nullable();
            $table->foreignId('transmitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('transmitted_candidate_ids')->nullable();
            $table->timestamps();

            $table->index(['recruiter_organization_id', 'status'], 'rpr_org_status_idx');
            $table->index('submitted_at', 'rpr_submitted_at_idx');
        });

        Schema::create('recruiter_profile_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('recruiter_profile_request_id')->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->string('feedback_status', 32)->nullable();
            $table->text('note')->nullable();
            $table->text('feedback_note')->nullable();
            $table->timestamp('feedback_updated_at')->nullable();
            $table->unsignedBigInteger('feedback_updated_by_user_id')->nullable();
            $table->json('visible_sections')->nullable();
            $table->json('masked_fields')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['recruiter_organization_id', 'candidate_user_id'], 'recruiter_assignments_org_candidate_idx');

            $table->foreign('recruiter_profile_request_id', 'rpa_profile_request_id_foreign')
                ->references('id')
                ->on('recruiter_profile_requests')
                ->nullOnDelete();

            $table->foreign('feedback_updated_by_user_id', 'rpa_feedback_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_profile_assignments');
        Schema::dropIfExists('recruiter_profile_requests');
        Schema::dropIfExists('recruiter_organization_user');
        Schema::dropIfExists('recruiter_organizations');
    }
};
