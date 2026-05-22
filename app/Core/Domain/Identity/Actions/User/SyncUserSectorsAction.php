<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Identity\Models\User;

final class SyncUserSectorsAction
{
    /**
     * @param  list<int>|null  $sectorIds  offer_category ids
     */
    public function execute(User $user, ?array $sectorIds): void
    {
        if ($sectorIds === null) {
            return;
        }

        $user->sectors()->sync(array_values(array_unique(array_map('intval', $sectorIds))));
    }
}
