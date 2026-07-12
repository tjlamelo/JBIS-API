<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Offer;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;
use Illuminate\Support\Facades\Log;

final class PublishScheduledOffersAction
{
    public function __construct(
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    /**
     * @return array{published: int, ids: list<int>}
     */
    public function execute(): array
    {
        $ids = Offer::query()
            ->dueForScheduledPublication()
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if ($ids === []) {
            return ['published' => 0, 'ids' => []];
        }

        $updated = Offer::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => OfferStatus::Published->value,
                'updated_at' => now(),
            ]);

        $this->catalogCache->invalidate();

        Log::info('[offers] Publications planifiées activées', [
            'count' => $updated,
            'ids' => $ids,
        ]);

        return ['published' => (int) $updated, 'ids' => $ids];
    }
}
