<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Settings;

use App\Core\Domain\Communication\Actions\SyncUserNewsletterFromSettingsAction;
use App\Core\Domain\Identity\DTOs\UserSettingsDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSetting;

final class UpdateUserSettingsAction
{
    public function __construct(
        private readonly EnsureUserSettingsAction $ensureUserSettings,
        private readonly SyncUserNewsletterFromSettingsAction $syncNewsletter,
    ) {}

    public function execute(User $user, UserSettingsDto $dto): UserSetting
    {
        $settings = $this->ensureUserSettings->execute($user);

        if ($dto->has('language') && $dto->language !== null) {
            $settings->language = $dto->language;
        }

        if ($dto->has('theme') && $dto->theme !== null) {
            $settings->theme = $dto->theme;
        }

        if ($dto->has('timezone') && $dto->timezone !== null) {
            $settings->timezone = $dto->timezone;
        }

        if ($dto->has('notifications') && $dto->notifications !== null) {
            $settings->notifications = array_replace_recursive(
                $settings->notifications ?? UserSetting::defaultNotifications(),
                $dto->notifications,
            );
        }

        if ($dto->has('privacy') && $dto->privacy !== null) {
            $settings->privacy = array_replace_recursive(
                $settings->privacy ?? UserSetting::defaultPrivacy(),
                $dto->privacy,
            );
        }

        if ($dto->has('marketing') && $dto->marketing !== null) {
            $settings->marketing = array_replace_recursive(
                $settings->marketing ?? UserSetting::defaultMarketing(),
                $dto->marketing,
            );
        }

        $settings->save();
        $settings->refresh();

        if ($dto->has('marketing') || $dto->has('language')) {
            $this->syncNewsletter->execute($user, $settings);
        }

        return $settings;
    }
}
