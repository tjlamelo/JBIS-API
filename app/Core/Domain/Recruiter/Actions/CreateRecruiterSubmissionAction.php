<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Enums\ProfileOrigin;
use App\Core\Domain\Recruiter\Enums\RecruiterSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateRecruiterSubmissionAction
{
    /**
     * @param  array{email: string, name?: string|null, phone_number1?: string|null}  $candidate
     */
    public function execute(RecruiterOrganization $organization, User $recruiter, array $candidate): RecruiterProfileSubmission
    {
        return DB::transaction(function () use ($organization, $recruiter, $candidate): RecruiterProfileSubmission {
            $email = strtolower(trim((string) $candidate['email']));
            if ($email === '') {
                throw new \InvalidArgumentException(__('Email candidat requis.'));
            }

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => (string) ($candidate['name'] ?? $email),
                    'phone_number1' => $candidate['phone_number1'] ?? null,
                    'password' => Hash::make(Str::password(16)),
                    'active' => true,
                ],
            );

            if (! $user->hasRole(ApplicationRole::CANDIDATE)) {
                $user->syncRoles([ApplicationRole::CANDIDATE]);
            }

            $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
            $profile->fill([
                'profile_origin' => ProfileOrigin::Recruiter,
                'recruiter_organization_id' => $organization->id,
                'is_approved' => false,
            ]);
            $profile->save();

            $submission = RecruiterProfileSubmission::query()->create([
                'recruiter_organization_id' => $organization->id,
                'submitted_by_user_id' => $recruiter->id,
                'candidate_user_id' => $user->id,
                'status' => RecruiterSubmissionStatus::Draft,
            ]);

            $profile->recruiter_submission_id = $submission->id;
            $profile->save();

            return $submission->load(['candidate.profile', 'submittedBy:id,name,email', 'organization:id,name,slug']);
        });
    }
}
