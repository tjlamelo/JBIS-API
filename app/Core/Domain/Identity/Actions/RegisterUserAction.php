<?php

namespace App\Core\Domain\Identity\Actions;

use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Services\UserDeviceSecurityService;
use Illuminate\Auth\Events\Registered;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisterUserAction
{
    public function __construct(
        private readonly CreatesNewUsers $createsNewUsers,
        private readonly UserDeviceSecurityService $userDeviceSecurityService,
        private readonly EnsureUserSettingsAction $ensureUserSettings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{user: User, access_token: string, token_type: string}
     */
    public function execute(array $data, DeviceContextDto $deviceContext): array
    {
        $name = (string) ($data['name'] ?? $data['user_name'] ?? '');

        /** @var User $user */
        $user = $this->createsNewUsers->create([
            'name' => $name,
            'email' => (string) $data['email'],
            'phone_number1' => $data['phone_number1'] ?? null,
            'password' => (string) $data['password'],
            'password_confirmation' => (string) ($data['password_confirmation'] ?? $data['password']),
        ]);

        event(new Registered($user));

        $this->ensureUserSettings->execute($user);

        $tokenName = (string) ($data['device_name'] ?? 'api');
        $accessToken = $user->createToken($tokenName);
        $token = $accessToken->plainTextToken;
        $assessment = $this->userDeviceSecurityService->assessLogin($user, $deviceContext);

        $this->userDeviceSecurityService->handleSuccessfulLogin(
            $user,
            $deviceContext,
            $assessment,
            $tokenName,
            $accessToken->accessToken->id,
        );

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
