<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Support;

use App\Core\Domain\Identity\Actions\Consent\ResolveUserConsentStatusAction;
use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Services\ResolveUserEffectivePermissions;

final class AuthUserPayloadMapper
{
    public function __construct(
        private readonly ProfileResponseMapper $profileResponseMapper,
        private readonly ResolveUserEffectivePermissions $resolvePermissions,
        private readonly UserSettingsPayloadMapper $settingsMapper,
        private readonly EnsureUserSettingsAction $ensureUserSettings,
        private readonly ResolveUserConsentStatusAction $resolveConsentStatus,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $user): array
    {
        $user->loadMissing(['profile.approver:id,name', 'profile.highestEducationLevel:id,name,slug', 'roles:id,name']);

        $profile = $user->profile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number1' => $user->phone_number1,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $this->resolvePermissions->execute($user),
            'profile' => $profile ? $this->profileResponseMapper->toArray($profile) : null,
            'settings' => $this->settingsMapper->toArray($this->ensureUserSettings->execute($user)),
            'consents' => $this->resolveConsentStatus->execute($user),
        ];
    }
}
