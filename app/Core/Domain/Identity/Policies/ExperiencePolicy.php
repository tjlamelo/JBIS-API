<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class ExperiencePolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'experience';
    }

    public function validate(User $user, Experience $experience): bool
    {
        return $this->moderate($user, $experience);
    }
}
