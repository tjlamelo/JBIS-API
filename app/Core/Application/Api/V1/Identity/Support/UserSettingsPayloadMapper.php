<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Support;

use App\Core\Domain\Identity\Models\UserSetting;

final class UserSettingsPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(UserSetting $settings): array
    {
        return [
            'language' => $settings->language,
            'theme' => $settings->theme,
            'timezone' => $settings->timezone,
            'notifications' => $settings->notifications ?? UserSetting::defaultNotifications(),
            'privacy' => $settings->privacy ?? UserSetting::defaultPrivacy(),
            'marketing' => $settings->marketing ?? UserSetting::defaultMarketing(),
            'updated_at' => $settings->updated_at?->toIso8601String(),
        ];
    }
}
