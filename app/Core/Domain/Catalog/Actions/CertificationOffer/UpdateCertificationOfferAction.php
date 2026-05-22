<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\CertificationOffer;

use App\Core\Domain\Catalog\DTOs\CertificationOffer\CertificationOfferDto;
use App\Core\Domain\Catalog\Mappers\CertificationOffer\CertificationOfferAttributeMapper;
use App\Core\Domain\Catalog\Models\CertificationOffer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateCertificationOfferAction
{
    public function __construct(
        private readonly CertificationOfferAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $certificationOfferId, CertificationOfferDto $dto): CertificationOffer
    {
        /** @var CertificationOffer|null $certificationOffer */
        $certificationOffer = CertificationOffer::query()->find($certificationOfferId);

        if (! $certificationOffer) {
            throw new ModelNotFoundException("CertificationOffer {$certificationOfferId} not found.");
        }

        $this->attributeMapper->apply($certificationOffer, $dto, isCreate: false);
        $certificationOffer->save();

        return $certificationOffer->refresh();
    }
}
