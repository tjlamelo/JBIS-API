<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterOfferSubmissionPolicy
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiteroffer', ApplicationPermission::VIEW));
    }

    public function view(User $user, RecruiterOfferSubmission $submission): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $submission->recruiter_organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiteroffer', ApplicationPermission::CREATE))
            && $this->access->primaryOrganization($user) !== null;
    }

    public function update(User $user, RecruiterOfferSubmission $submission): bool
    {
        if ($user->can(ApplicationPermission::name('recruiteroffer', ApplicationPermission::UPDATE))
            && $user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $submission->recruiter_organization_id)
            && $submission->isEditableByRecruiter();
    }

    public function review(User $user, RecruiterOfferSubmission $submission): bool
    {
        return $user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('recruiteroffer', ApplicationPermission::UPDATE));
    }
}
