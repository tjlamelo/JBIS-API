<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferType;

use App\Core\Domain\Catalog\Models\OfferType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteOfferTypeAction
{
    public function execute(int $offerTypeId): bool
    {
        /** @var OfferType|null $offerType */
        $offerType = OfferType::query()->find($offerTypeId);

        if (! $offerType) {
            throw new ModelNotFoundException("OfferType {$offerTypeId} not found.");
        }

        return (bool) $offerType->delete();
    }
}
