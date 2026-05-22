<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class EducationPolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'education';
    }

    public function approve(User $user, Education $education): bool
    {
        return $this->moderate($user, $education);
    }
}
