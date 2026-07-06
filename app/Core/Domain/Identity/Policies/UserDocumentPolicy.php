<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Policies\Concerns\ChecksUserOwnedResource;

final class UserDocumentPolicy
{
    use ChecksUserOwnedResource;

    protected function resourceKey(): string
    {
        return 'userdocument';
    }

    public function view(User $user, UserDocument $document): bool
    {
        if ($this->permission($user, 'view')) {
            return true;
        }

        if (! $document->belongsToUser((int) $user->id)) {
            return false;
        }

        return ! (bool) $document->is_sensitive;
    }

    public function update(User $user, UserDocument $document): bool
    {
        return $this->permission($user, 'update')
            || $document->belongsToUser((int) $user->id);
    }

    public function delete(User $user, UserDocument $document): bool
    {
        return $this->permission($user, 'delete')
            || $document->belongsToUser((int) $user->id);
    }

    public function validateAny(User $user): bool
    {
        return $this->permission($user, 'update');
    }

    public function validate(User $user, UserDocument $document): bool
    {
        return $this->validateAny($user);
    }

    public function download(User $user, UserDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
