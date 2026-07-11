<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Core\Domain\Partner\Support\PartnerAccess;

final class PartnerCohortPolicy
{
    public function __construct(private readonly PartnerAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnercohort', ApplicationPermission::VIEW));
    }

    public function view(User $user, PartnerCohort $cohort): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $cohort->partner_organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnercohort', ApplicationPermission::CREATE))
            && $this->access->primaryOrganization($user) !== null;
    }

    public function update(User $user, PartnerCohort $cohort): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('partnercohort', ApplicationPermission::UPDATE))) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $cohort->partner_organization_id)
            && $cohort->isEditableByPartner();
    }

    public function review(User $user, PartnerCohort $cohort): bool
    {
        return $user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('partnercohort', ApplicationPermission::UPDATE));
    }
}
