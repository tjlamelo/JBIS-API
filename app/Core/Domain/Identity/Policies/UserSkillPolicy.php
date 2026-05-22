<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class UserSkillPolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'userskill';
    }
}
