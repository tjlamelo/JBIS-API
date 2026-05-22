<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies\Concerns;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use Illuminate\Database\Eloquent\Model;

/**
 * Permissions Spatie + accès propriétaire (user_id) pour les ressources du dossier candidat.
 */
trait ChecksUserOwnedResource
{
    abstract protected function resourceKey(): string;

    protected function permission(User $user, string $action): bool
    {
        return $user->can(ApplicationPermission::name($this->resourceKey(), $action));
    }

    protected function owns(User $user, Model $model): bool
    {
        return (int) $user->id === (int) $model->getAttribute('user_id');
    }

    /** Liste : staff avec .view ; candidat limité au filtre controller. */
    public function viewAny(User $user): bool
    {
        return $this->permission($user, ApplicationPermission::VIEW) || true;
    }

    public function view(User $user, Model $model): bool
    {
        return $this->permission($user, ApplicationPermission::VIEW) || $this->owns($user, $model);
    }

    public function store(User $user, User $target): bool
    {
        return $this->permission($user, ApplicationPermission::CREATE) || $this->owns($user, $target);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->permission($user, ApplicationPermission::UPDATE) || $this->owns($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->permission($user, ApplicationPermission::DELETE) || $this->owns($user, $model);
    }

    /** Validation / approbation staff (pas le candidat). */
    public function moderate(User $user, Model $model): bool
    {
        return $this->permission($user, ApplicationPermission::UPDATE);
    }
}
