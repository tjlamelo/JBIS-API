<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\OfferType;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\OfferType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateOfferTypeAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(int $offerTypeId, CatalogNameSlugDto $dto): OfferType
    {
        /** @var OfferType|null $offerType */
        $offerType = OfferType::query()->find($offerTypeId);

        if (! $offerType) {
            throw new ModelNotFoundException("OfferType {$offerTypeId} not found.");
        }

        $this->nameSlugMapper->applyNameAndSlug($offerType, $dto->provided_keys, $dto->name, $dto->slug, false);
        $offerType->save();

        return $offerType->refresh();
    }
}
