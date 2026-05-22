<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferType;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\OfferType;

final class CreateOfferTypeAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(CatalogNameSlugDto $dto): OfferType
    {
        $offerType = new OfferType;
        $this->nameSlugMapper->applyNameAndSlug($offerType, $dto->provided_keys, $dto->name, $dto->slug, true);
        $offerType->save();

        return $offerType->refresh();
    }
}
