<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Education;

use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\User;

final class StoreEducationAction
{
    public function execute(User $user, array $attributes): Education
    {
        return Education::query()->create([
            ...$attributes,
            'user_id' => $user->id,
        ]);
    }
}
