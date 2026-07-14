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
        $user->loadMissing([
            'profile.approver:id,name',
            'profile.highestEducationLevel:id,name,slug',
            'roles:id,name',
            'trades:id,name,slug,category_id',
            'trades.category:id,name,slug',
        ]);

        $profile = $user->profile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number1' => $user->phone_number1,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at,
            'auth_provider' => (string) ($user->auth_provider ?? 'local'),
            'must_change_password' => (bool) $user->must_change_password,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $this->resolvePermissions->execute($user),
            'profile' => $profile ? $this->profileResponseMapper->toArray($profile) : null,
            'trades' => $user->trades->map(static fn ($trade) => [
                'id' => $trade->id,
                'slug' => $trade->slug,
                'name' => $trade->getTranslations('name'),
                'category_id' => $trade->category_id,
                'category' => $trade->relationLoaded('category') && $trade->category !== null
                    ? [
                        'id' => $trade->category->id,
                        'slug' => $trade->category->slug,
                        'name' => $trade->category->getTranslations('name'),
                    ]
                    : null,
                'years_of_experience' => $trade->pivot?->years_of_experience,
            ])->values()->all(),
            'sectors' => $user->sectorsFromTrades()->map(static fn ($sector) => [
                'id' => $sector->id,
                'slug' => $sector->slug,
                'name' => $sector->getTranslations('name'),
            ])->values()->all(),
            'settings' => $this->settingsMapper->toArray($this->ensureUserSettings->execute($user)),
            'consents' => $this->resolveConsentStatus->execute($user),
        ];
    }
}
