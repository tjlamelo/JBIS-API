<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\ContractType;

use App\Core\Domain\Catalog\DTOs\ContractType\ContractTypeDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\ContractType;

final class ContractTypeAttributeMapper
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function apply(ContractType $contractType, ContractTypeDto $dto, bool $isCreate): void
    {
        $this->nameSlugMapper->applyNameAndSlug(
            $contractType,
            $dto->provided_keys,
            $dto->name,
            $dto->slug,
            $isCreate,
        );

        if ($isCreate || $dto->has('color_code')) {
            $contractType->color_code = $dto->color_code;
        }
    }
}
