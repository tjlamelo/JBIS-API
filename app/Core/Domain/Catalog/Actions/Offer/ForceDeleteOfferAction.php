<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Offer;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ForceDeleteOfferAction
{
    public function __construct(
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    public function execute(int $offerId): bool
    {
        /** @var Offer|null $offer */
        $offer = Offer::query()->withTrashed()->find($offerId);

        if (! $offer) {
            throw new ModelNotFoundException("Offer {$offerId} not found.");
        }

        $deleted = (bool) $offer->forceDelete();

        if ($deleted) {
            $this->catalogCache->invalidate();
        }

        return $deleted;
    }
}
