<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Permission;

use App\Core\Domain\Identity\Enums\PermissionOverrideEffect;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserPermissionOverride;
use Illuminate\Support\Collection;

final class SetUserPermissionOverridesAction
{
    /**
     * @param  list<array{permission_name: string, effect: PermissionOverrideEffect|string}>  $overrides
     * @return Collection<int, UserPermissionOverride>
     */
    public function execute(User $user, array $overrides): Collection
    {
        $saved = collect();

        foreach ($overrides as $row) {
            $effect = $row['effect'] instanceof PermissionOverrideEffect
                ? $row['effect']
                : PermissionOverrideEffect::from((string) $row['effect']);

            $override = UserPermissionOverride::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'permission_name' => $row['permission_name'],
                ],
                ['effect' => $effect],
            );

            $saved->push($override);
        }

        $user->flushPermissionOverridesCache();

        return $saved;
    }
}
