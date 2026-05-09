<?php

namespace App\Core\Application\Api\V1\Auth\Actions;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class CompleteTwoFactorLoginAction
{
    private const MAX_ATTEMPTS = 5;

    /**
     * @param  array{challenge_token: string, code?: string|null, recovery_code?: string|null}  $payload
     * @return array{status: 'ok'|'error', http_status: int, data: array<string, mixed>}
     */
    public function execute(array $payload, TwoFactorAuthenticationProvider $provider): array
    {
        $challengeToken = (string) $payload['challenge_token'];
        $cacheKey = $this->cacheKey($challengeToken);
        $challenge = Cache::get($cacheKey);

        if (! is_array($challenge) || ! isset($challenge['user_id'])) {
            return $this->error(422, __('Challenge 2FA invalide ou expire.'));
        }

        $attempts = (int) ($challenge['attempts'] ?? 0);
        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);

            return $this->error(422, __('Trop de tentatives 2FA. Veuillez vous reconnecter.'));
        }

        $user = User::query()->find($challenge['user_id']);
        if (! $user) {
            Cache::forget($cacheKey);

            return $this->error(422, __('Utilisateur introuvable pour ce challenge 2FA.'));
        }

        $isValid = false;
        $providedRecoveryCode = (string) ($payload['recovery_code'] ?? '');

        if ($providedRecoveryCode !== '') {
            $matchedRecoveryCode = collect($user->recoveryCodes())->first(
                fn (string $storedCode): bool => hash_equals($storedCode, $providedRecoveryCode)
            );

            if ($matchedRecoveryCode) {
                $user->replaceRecoveryCode($matchedRecoveryCode);
                $isValid = true;
            }
        } else {
            $providedCode = (string) ($payload['code'] ?? '');
            if ($providedCode !== '' && ! empty($user->two_factor_secret)) {
                $isValid = $provider->verify(
                    Fortify::currentEncrypter()->decrypt($user->two_factor_secret),
                    $providedCode
                );
            }
        }

        if (! $isValid) {
            $challenge['attempts'] = $attempts + 1;

            if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($cacheKey);

                return $this->error(422, __('Trop de tentatives 2FA. Veuillez vous reconnecter.'));
            }

            Cache::put($cacheKey, $challenge, now()->addMinutes(5));

            return $this->error(422, __('Code 2FA invalide.'));
        }

        Cache::forget($cacheKey);

        $tokenName = (string) ($challenge['device_name'] ?? 'api');
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'status' => 'ok',
            'http_status' => 200,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'message' => __('Connexion 2FA reussie.'),
            ],
        ];
    }

    /**
     * @return array{status: 'error', http_status: int, data: array{message: string}}
     */
    private function error(int $httpStatus, string $message): array
    {
        return [
            'status' => 'error',
            'http_status' => $httpStatus,
            'data' => [
                'message' => $message,
            ],
        ];
    }

    private function cacheKey(string $challengeToken): string
    {
        return 'auth:2fa:challenge:'.$challengeToken;
    }
}
