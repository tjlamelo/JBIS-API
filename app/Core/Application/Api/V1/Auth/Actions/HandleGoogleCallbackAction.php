<?php

namespace App\Core\Application\Api\V1\Auth\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class HandleGoogleCallbackAction
{
    /**
     * @return array{success: bool, value: string}
     */
    public function execute(string $frontUrl): array
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $googleProvider */
        $googleProvider = Socialite::driver('google');

        try {
            $googleUser = $googleProvider
                ->stateless()
                ->user();
        } catch (Exception|RequestException) {
            return [
                'success' => false,
                'value' => 'oauth_failed',
            ];
        }

        $email = (string) ($googleUser->getEmail() ?? '');

        if ($email === '') {
            return [
                'success' => false,
                'value' => 'email_required',
            ];
        }

        $name = (string) ($googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User');

        $user = User::query()->where('email', $email)->first();

        if ($user !== null && $user->auth_provider !== 'google') {
            return [
                'success' => false,
                'value' => 'password_account',
            ];
        }

        if ($user === null) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'active' => true,
                'auth_provider' => 'google',
            ]);
            $user->assignRole(ApplicationRole::CANDIDATE);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $googleAvatarUrl = (string) ($googleUser->getAvatar() ?? '');
        if ($googleAvatarUrl !== '') {
            UserProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['profile_picture' => $googleAvatarUrl]
            );
        }

        $token = $user->createToken('google-oauth')->plainTextToken;

        return [
            'success' => true,
            'value' => $frontUrl.'/login?google_token='.urlencode($token),
        ];
    }
}
