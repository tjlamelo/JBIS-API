<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Policies\CertificationOffer;

use App\Core\Domain\Catalog\Models\CertificationOffer;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class CertificationOfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('certificationoffer', ApplicationPermission::VIEW));
    }

    public function view(User $user, CertificationOffer $certificationOffer): bool
    {
        return $user->can(ApplicationPermission::name('certificationoffer', ApplicationPermission::VIEW));
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('certificationoffer', ApplicationPermission::CREATE));
    }

    public function update(User $user, CertificationOffer $certificationOffer): bool
    {
        return $user->can(ApplicationPermission::name('certificationoffer', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, CertificationOffer $certificationOffer): bool
    {
        return $user->can(ApplicationPermission::name('certificationoffer', ApplicationPermission::DELETE));
    }
}
