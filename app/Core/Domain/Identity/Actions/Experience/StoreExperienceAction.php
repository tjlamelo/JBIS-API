<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Experience;

use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\User;

final class StoreExperienceAction
{
    public function execute(User $user, array $attributes): Experience
    {
        return Experience::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'status' => $attributes['status'] ?? 'PENDING',
        ]);
    }
}
