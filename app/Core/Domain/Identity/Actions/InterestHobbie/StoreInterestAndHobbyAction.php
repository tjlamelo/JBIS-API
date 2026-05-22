<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\InterestHobbie;

use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\User;

final class StoreInterestAndHobbyAction
{
    public function execute(User $user, array $attributes): InterestAndHobby
    {
        return InterestAndHobby::query()->create([
            ...$attributes,
            'user_id' => $user->id,
        ]);
    }
}
