<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services;

use App\Core\Domain\Identity\Enums\PermissionOverrideEffect;
use App\Core\Domain\Identity\Models\User;
/**
 * Liste effective des permissions (rôles Spatie + overrides allow/deny).
 */
final class ResolveUserEffectivePermissions
{
    /**
     * @return list<string>
     */
    public function execute(User $user): array
    {
        $user->loadMissing(['roles', 'permissions', 'permissionOverrides']);

        $granted = $user->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        foreach ($user->permissionOverrides as $override) {
            $name = $override->permission_name;
            if ($override->effect === PermissionOverrideEffect::Allow) {
                if (! in_array($name, $granted, true)) {
                    $granted[] = $name;
                }
            } elseif ($override->effect === PermissionOverrideEffect::Deny) {
                $granted = array_values(array_filter(
                    $granted,
                    static fn (string $permission): bool => $permission !== $name,
                ));
            }
        }

        sort($granted);

        return $granted;
    }
}
