<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Offer;

use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ForceDeleteOfferAction
{
    public function execute(int $offerId): bool
    {
        /** @var Offer|null $offer */
        $offer = Offer::query()->withTrashed()->find($offerId);

        if (! $offer) {
            throw new ModelNotFoundException("Offer {$offerId} not found.");
        }

        return (bool) $offer->forceDelete();
    }
}
