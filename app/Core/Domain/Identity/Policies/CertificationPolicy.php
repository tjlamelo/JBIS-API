<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class CertificationPolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'certification';
    }

    public function validate(User $user, Certification $certification): bool
    {
        return $this->moderate($user, $certification);
    }
}
