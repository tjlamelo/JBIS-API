<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Support;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Partner\Models\PartnerOrganization;

final class PartnerAccess
{
    public function primaryOrganization(User $user): ?PartnerOrganization
    {
        if (! $user->hasRole(ApplicationRole::PARTNER)) {
            return null;
        }

        /** @var PartnerOrganization|null $organization */
        $organization = $user->partnerOrganizations()->orderByPivot('is_owner', 'desc')->first();

        return $organization;
    }

    public function belongsToOrganization(User $user, int $organizationId): bool
    {
        return $user->partnerOrganizations()->where('partner_organizations.id', $organizationId)->exists();
    }
}
