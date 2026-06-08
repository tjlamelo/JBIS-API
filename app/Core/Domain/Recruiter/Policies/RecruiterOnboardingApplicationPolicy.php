<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;

final class RecruiterOnboardingApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiteronboarding', ApplicationPermission::VIEW));
    }

    public function view(User $user, RecruiterOnboardingApplication $application): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $application->applicant_user_id === $user->id;
    }

    public function create(?User $user): bool
    {
        return (bool) config('services.recruiter.onboarding_enabled', true);
    }

    public function review(User $user, RecruiterOnboardingApplication $application): bool
    {
        return $user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('recruiteronboarding', ApplicationPermission::UPDATE));
    }
}
