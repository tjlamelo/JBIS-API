<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferCategory;

use App\Core\Domain\Catalog\Models\OfferCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteOfferCategoryAction
{
    public function execute(int $offerCategoryId): bool
    {
        /** @var OfferCategory|null $category */
        $category = OfferCategory::query()->find($offerCategoryId);

        if (! $category) {
            throw new ModelNotFoundException("OfferCategory {$offerCategoryId} not found.");
        }

        return (bool) $category->delete();
    }
}
