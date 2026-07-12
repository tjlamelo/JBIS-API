<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Communication\Actions\NotifyStaffWelcomeAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAdminUserAction
{
    public function __construct(
        private readonly NotifyStaffWelcomeAction $notifyStaffWelcome,
    ) {}

    public function execute(User $user, AdminUserWriteDto $dto): User
    {
        $rolesBefore = $user->getRoleNames()->map(static fn ($role) => (string) $role)->values()->all();

        $updated = DB::transaction(function () use ($user, $dto): User {
            $attributes = $dto->toUserAttributes();

            if ($attributes !== []) {
                $user->fill($attributes);
                $user->save();
            }

            if ($dto->roles !== null) {
                $user->syncRoles($dto->roles);
            }

            return $user->load(['roles:id,name', 'profile.approver:id,name', 'trades:id,name,slug,category_id', 'trades.category:id,name,slug']);
        });

        $rolesAfter = $dto->roles;
        $this->notifyStaffWelcome->ifBecameStaff($updated, $rolesBefore, $rolesAfter);

        return $updated;
    }
}
