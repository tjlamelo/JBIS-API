<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Training;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserTraining;

final class EnrollUserTrainingAction
{
    public function execute(User $user, array $attributes): UserTraining
    {
        return UserTraining::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'status' => $attributes['status'] ?? 'ONGOING',
        ]);
    }
}
