<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferCategory;

use App\Core\Domain\Catalog\DTOs\OfferCategory\OfferCategoryDto;
use App\Core\Domain\Catalog\Mappers\OfferCategory\OfferCategoryAttributeMapper;
use App\Core\Domain\Catalog\Models\OfferCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateOfferCategoryAction
{
    public function __construct(
        private readonly OfferCategoryAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $offerCategoryId, OfferCategoryDto $dto): OfferCategory
    {
        /** @var OfferCategory|null $category */
        $category = OfferCategory::query()->find($offerCategoryId);

        if (! $category) {
            throw new ModelNotFoundException("OfferCategory {$offerCategoryId} not found.");
        }

        $this->attributeMapper->apply($category, $dto, isCreate: false);
        $category->save();

        return $category->refresh();
    }
}
