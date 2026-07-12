<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Communication\Actions\NotifyStaffWelcomeAction;
use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Support\Facades\DB;

final class CreateAdminUserAction
{
    public function __construct(
        private readonly NotifyStaffWelcomeAction $notifyStaffWelcome,
        private readonly EnsureUserSettingsAction $ensureUserSettings,
    ) {}

    public function execute(AdminUserWriteDto $dto): User
    {
        $user = DB::transaction(function () use ($dto): User {
            $attributes = $dto->toUserAttributes();
            $attributes['active'] = $dto->active ?? true;

            /** @var User $user */
            $user = User::query()->create($attributes);

            $roles = $dto->roles ?? [ApplicationRole::CANDIDATE];
            if ($roles !== []) {
                $user->syncRoles($roles);
            }

            if ($dto->hasProfileData()) {
                UserProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    $dto->toProfileAttributes(),
                );
            }

            $this->ensureUserSettings->execute($user);

            return $user->load(['roles:id,name', 'profile.approver:id,name', 'trades:id,name,slug,category_id', 'trades.category:id,name,slug']);
        });

        $this->notifyStaffWelcome->ifBecameStaff($user, [], $dto->roles ?? [ApplicationRole::CANDIDATE]);

        return $user;
    }
}
