<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAdminUserAction
{
    public function __construct(
        private readonly SyncUserSectorsAction $syncUserSectors,
    ) {}

    public function execute(User $user, AdminUserWriteDto $dto): User
    {
        return DB::transaction(function () use ($user, $dto): User {
            $attributes = $dto->toUserAttributes();

            if ($attributes !== []) {
                $user->fill($attributes);
                $user->save();
            }

            if ($dto->roles !== null) {
                $user->syncRoles($dto->roles);
            }

            $this->syncUserSectors->execute($user, $dto->sector_ids);

            return $user->load(['roles:id,name', 'profile.approver:id,name', 'sectors:id,name,slug']);
        });
    }
}
