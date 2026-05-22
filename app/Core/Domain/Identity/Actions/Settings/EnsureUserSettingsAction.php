<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Settings;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSetting;

final class EnsureUserSettingsAction
{
    public function execute(User $user): UserSetting
    {
        return UserSetting::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'language' => 'fr',
                'theme' => 'system',
                'timezone' => 'Africa/Douala',
                'notifications' => UserSetting::defaultNotifications(),
                'privacy' => UserSetting::defaultPrivacy(),
                'marketing' => UserSetting::defaultMarketing(),
            ],
        );
    }
}
