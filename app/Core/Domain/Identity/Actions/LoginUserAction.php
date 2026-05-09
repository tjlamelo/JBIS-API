<?php

namespace App\Core\Domain\Identity\Actions;

use App\Core\Domain\Identity\DTOs\AuthenticationResultDto;
use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\DTOs\LoginCredentialsDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Services\UserDeviceSecurityService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function __construct(
        private readonly UserDeviceSecurityService $userDeviceSecurityService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(
        LoginCredentialsDto $credentials,
        DeviceContextDto $deviceContext,
        bool $issueToken = true,
    ): AuthenticationResultDto
    {
        $user = User::query()->findByLogin($credentials->login)->first();

        if (! $user || ! Hash::check($credentials->password, (string) $user->password)) {
            if ($user) {
                $this->userDeviceSecurityService->handleFailedLogin($user, $deviceContext);
            }

            throw ValidationException::withMessages([
                'login' => [__('Les identifiants fournis sont incorrects.')],
            ]);
        }

        $tokenName = $credentials->deviceName;
        $token = '';

        if ($issueToken) {
            $token = $user->createToken($tokenName)->plainTextToken;
            $this->userDeviceSecurityService->handleSuccessfulLogin($user, $deviceContext, $tokenName);
        }

        return new AuthenticationResultDto(
            user: $user,
            accessToken: $token,
        );
    }
}
