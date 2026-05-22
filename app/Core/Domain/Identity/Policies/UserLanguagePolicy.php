<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class UserLanguagePolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'userlanguage';
    }

    public function approve(User $user, Language $userLanguage): bool
    {
        return $this->moderate($user, $userLanguage);
    }
}
