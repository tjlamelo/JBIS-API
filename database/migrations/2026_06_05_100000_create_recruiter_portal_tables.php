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

        Schema::create('recruiter_profile_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('active')->index();
            $table->text('note')->nullable();
            $table->json('visible_sections')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['recruiter_organization_id', 'candidate_user_id'], 'recruiter_assignments_org_candidate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_profile_assignments');
        Schema::dropIfExists('recruiter_organization_user');
        Schema::dropIfExists('recruiter_organizations');
    }
};
