<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Intership;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserInternship;

final class StoreUserInternshipAction
{
    public function execute(User $user, array $attributes): UserInternship
    {
        return UserInternship::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'status' => $attributes['status'] ?? 'ONGOING',
        ]);
    }
}
