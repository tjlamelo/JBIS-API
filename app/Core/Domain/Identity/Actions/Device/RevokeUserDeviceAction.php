<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Device;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDevice;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Sanctum\PersonalAccessToken;

final class RevokeUserDeviceAction
{
    public function execute(User $user, int $deviceId, ?int $currentTokenId = null): void
    {
        /** @var UserDevice|null $device */
        $device = UserDevice::query()
            ->where('user_id', $user->id)
            ->whereKey($deviceId)
            ->first();

        if (! $device) {
            throw new ModelNotFoundException('Device not found.');
        }

        if ($currentTokenId !== null && (int) $device->personal_access_token_id === $currentTokenId) {
            throw new \InvalidArgumentException('Cannot revoke the current session from this endpoint.');
        }

        if ($device->personal_access_token_id) {
            PersonalAccessToken::query()
                ->where('id', $device->personal_access_token_id)
                ->where('tokenable_id', $user->id)
                ->where('tokenable_type', $user->getMorphClass())
                ->delete();
        }

        $device->delete();
    }
}
