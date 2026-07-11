<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Partner\Models\PartnerOrganization;
use App\Core\Domain\Partner\Support\PartnerAccess;

final class PartnerOrganizationPolicy
{
    public function __construct(private readonly PartnerAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnerorganization', ApplicationPermission::VIEW));
    }

    public function view(User $user, PartnerOrganization $organization): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $organization->id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('partnerorganization', ApplicationPermission::CREATE));
    }

    public function update(User $user, PartnerOrganization $organization): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return $user->can(ApplicationPermission::name('partnerorganization', ApplicationPermission::UPDATE));
        }

        return $this->access->belongsToOrganization($user, $organization->id);
    }
}
