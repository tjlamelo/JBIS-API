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
        return $this->permission($user, 'view')
            || $document->belongsToUser((int) $user->id);
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

    public function validate(User $user, UserDocument $document): bool
    {
        return $this->permission($user, 'update');
    }

    public function download(User $user, UserDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
