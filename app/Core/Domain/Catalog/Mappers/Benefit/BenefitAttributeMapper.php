<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Benefit;

use App\Core\Domain\Catalog\DTOs\Benefit\BenefitDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\Benefit;

final class BenefitAttributeMapper
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function apply(Benefit $benefit, BenefitDto $dto, bool $isCreate): void
    {
        $this->nameSlugMapper->applyNameAndSlug(
            $benefit,
            $dto->provided_keys,
            $dto->name,
            $dto->slug,
            $isCreate,
        );

        if ($isCreate || $dto->has('icon')) {
            $benefit->icon = $dto->icon;
        }
    }
}
