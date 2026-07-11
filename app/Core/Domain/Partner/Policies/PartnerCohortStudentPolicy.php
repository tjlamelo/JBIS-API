<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Partner\Models\PartnerCohortStudent;
use App\Core\Domain\Partner\Support\PartnerAccess;

final class PartnerCohortStudentPolicy
{
    public function __construct(private readonly PartnerAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnercohortstudent', ApplicationPermission::VIEW));
    }

    public function view(User $user, PartnerCohortStudent $student): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        $student->loadMissing('cohort');

        return $this->access->belongsToOrganization($user, $student->cohort->partner_organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnercohortstudent', ApplicationPermission::CREATE))
            && $this->access->primaryOrganization($user) !== null;
    }

    public function update(User $user, PartnerCohortStudent $student): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('partnercohortstudent', ApplicationPermission::UPDATE))) {
            return true;
        }

        $student->loadMissing('cohort');

        return $this->access->belongsToOrganization($user, $student->cohort->partner_organization_id)
            && $student->cohort->isEditableByPartner();
    }
}
