<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Identity\Models\User;

final class SyncUserTradesAction
{
    public function __construct(
        private readonly SyncUserSectorsAction $syncUserSectors,
    ) {}

    /**
     * @param  list<array{trade_id: int, years_of_experience?: int|null}>  $trades
     */
    public function execute(User $user, array $trades): void
    {
        $sync = [];
        foreach ($trades as $row) {
            $tradeId = (int) ($row['trade_id'] ?? 0);
            if ($tradeId <= 0) {
                continue;
            }
            $years = $row['years_of_experience'] ?? null;
            $sync[$tradeId] = [
                'years_of_experience' => $years !== null && $years !== '' ? (int) $years : null,
            ];
        }

        $user->trades()->sync($sync);

        if ($sync === []) {
            return;
        }

        $categoryIds = Trade::query()
            ->whereIn('id', array_keys($sync))
            ->pluck('category_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->syncUserSectors->execute($user, $categoryIds);
    }
}
