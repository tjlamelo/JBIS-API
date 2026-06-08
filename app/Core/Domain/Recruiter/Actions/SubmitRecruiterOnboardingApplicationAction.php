<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Enums\RecruiterOnboardingStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class SubmitRecruiterOnboardingApplicationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?User $existingUser = null): RecruiterOnboardingApplication
    {
        return DB::transaction(function () use ($data, $existingUser): RecruiterOnboardingApplication {
            $user = $existingUser ?? $this->createApplicantUser($data);

            if ($existingUser !== null) {
                $data['contact_email'] = $existingUser->email;
            }

            $application = RecruiterOnboardingApplication::query()->updateOrCreate(
                ['applicant_user_id' => $user->id],
                [
                    'company_name' => (string) $data['company_name'],
                    'legal_form' => $data['legal_form'] ?? null,
                    'registration_number' => $data['registration_number'] ?? null,
                    'contact_name' => (string) $data['contact_name'],
                    'contact_email' => (string) $data['contact_email'],
                    'contact_phone' => $data['contact_phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'website' => $data['website'] ?? null,
                    'activity_description' => $data['activity_description'] ?? null,
                    'desired_slug' => $data['desired_slug'] ?? null,
                    'status' => RecruiterOnboardingStatus::Submitted,
                    'submitted_at' => now(),
                    'rejection_reason' => null,
                ],
            );

            return $application->fresh(['documents', 'applicant']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createApplicantUser(array $data): User
    {
        $user = User::query()->create([
            'name' => (string) $data['contact_name'],
            'email' => (string) $data['contact_email'],
            'phone_number1' => $data['contact_phone'] ?? null,
            'password' => Hash::make((string) $data['password']),
            'email_verified_at' => now(),
            'active' => true,
        ]);

        $user->assignRole(ApplicationRole::CANDIDATE);

        return $user;
    }
}
