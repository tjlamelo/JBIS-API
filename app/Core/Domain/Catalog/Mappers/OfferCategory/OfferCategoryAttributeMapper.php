<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\OfferCategory;

use App\Core\Domain\Catalog\DTOs\OfferCategory\OfferCategoryDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\OfferCategory;

final class OfferCategoryAttributeMapper
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function apply(OfferCategory $offerCategory, OfferCategoryDto $dto, bool $isCreate): void
    {
        $this->nameSlugMapper->applyNameAndSlug(
            $offerCategory,
            $dto->provided_keys,
            $dto->name,
            $dto->slug,
            $isCreate,
        );

        if ($isCreate || $dto->has('icon')) {
            $offerCategory->icon = $dto->icon;
        }

        if ($isCreate || $dto->has('is_active')) {
            $offerCategory->is_active = $dto->is_active;
        }
    }
}
