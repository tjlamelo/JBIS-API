<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Concerns;

use App\Core\Domain\Identity\Enums\PermissionOverrideEffect;
use App\Core\Domain\Identity\Models\UserPermissionOverride;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;

trait HasPermissionOverrides
{
    /** @var Collection<string, PermissionOverrideEffect>|null */
    protected ?Collection $permissionOverridesCache = null;

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    public function flushPermissionOverridesCache(): void
    {
        $this->permissionOverridesCache = null;
    }

    public function permissionOverrideEffect(string $permissionName): ?PermissionOverrideEffect
    {
        return $this->getPermissionOverridesCache()->get($permissionName);
    }

    /**
     * @return Collection<string, PermissionOverrideEffect>
     */
    protected function getPermissionOverridesCache(): Collection
    {
        if ($this->permissionOverridesCache !== null) {
            return $this->permissionOverridesCache;
        }

        if ($this->relationLoaded('permissionOverrides')) {
            $this->permissionOverridesCache = $this->permissionOverrides
                ->mapWithKeys(fn (UserPermissionOverride $row): array => [
                    $row->permission_name => $row->effect,
                ]);
        } else {
            $this->permissionOverridesCache = $this->permissionOverrides()
                ->get()
                ->mapWithKeys(fn (UserPermissionOverride $row): array => [
                    $row->permission_name => $row->effect,
                ]);
        }

        return $this->permissionOverridesCache;
    }

    protected function resolvePermissionName(mixed $permission): string
    {
        if (is_string($permission)) {
            return $permission;
        }

        if ($permission instanceof PermissionContract) {
            return $permission->name;
        }

        if ($permission instanceof \BackedEnum) {
            return (string) $permission->value;
        }

        return (string) $permission;
    }

    protected function applyPermissionOverrideBeforeRoleCheck(mixed $permission): ?bool
    {
        $permissionName = $this->resolvePermissionName($permission);
        $override = $this->permissionOverrideEffect($permissionName);

        if ($override === PermissionOverrideEffect::Deny) {
            return false;
        }
        if ($override === PermissionOverrideEffect::Allow) {
            return true;
        }

        return null;
    }
}
