<?php

namespace App\Core\Application\Api\V1\Auth\Actions;

use App\Core\Domain\Identity\DTOs\AuthenticationResultDto;
use App\Core\Domain\Identity\DTOs\LoginCredentialsDto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PrepareTwoFactorLoginChallengeAction
{
    /**
     * @return array{two_factor_required: bool, challenge_token: string, message: string}
     */
    public function execute(AuthenticationResultDto $payload, LoginCredentialsDto $credentials): array
    {
        $payload->user->tokens()
            ->where('name', $credentials->deviceName)
            ->latest('id')
            ->limit(1)
            ->delete();

        $challengeToken = Str::random(64);

        Cache::put(
            $this->cacheKey($challengeToken),
            [
                'user_id' => $payload->user->id,
                'device_name' => $credentials->deviceName,
                'created_at' => now()->timestamp,
                'attempts' => 0,
            ],
            now()->addMinutes(5)
        );

        return [
            'two_factor_required' => true,
            'challenge_token' => $challengeToken,
            'message' => __('Code 2FA requis pour finaliser la connexion.'),
        ];
    }

    private function cacheKey(string $challengeToken): string
    {
        return 'auth:2fa:challenge:'.$challengeToken;
    }
}
