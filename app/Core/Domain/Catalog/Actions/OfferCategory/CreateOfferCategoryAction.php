<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferCategory;

use App\Core\Domain\Catalog\DTOs\OfferCategory\OfferCategoryDto;
use App\Core\Domain\Catalog\Mappers\OfferCategory\OfferCategoryAttributeMapper;
use App\Core\Domain\Catalog\Models\OfferCategory;

final class CreateOfferCategoryAction
{
    public function __construct(
        private readonly OfferCategoryAttributeMapper $attributeMapper,
    ) {}

    public function execute(OfferCategoryDto $dto): OfferCategory
    {
        $category = new OfferCategory;
        $this->attributeMapper->apply($category, $dto, isCreate: true);
        $category->save();

        return $category->refresh();
    }
}
