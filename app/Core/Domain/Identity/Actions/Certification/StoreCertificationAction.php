<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Certification;

use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\User;

final class StoreCertificationAction
{
    public function execute(User $user, array $attributes): Certification
    {
        return Certification::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'status' => $attributes['status'] ?? 'PENDING',
        ]);
    }
}
