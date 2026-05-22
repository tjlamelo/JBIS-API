<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\CertificationOffer;

use App\Core\Domain\Catalog\DTOs\CertificationOffer\CertificationOfferDto;
use App\Core\Domain\Catalog\Mappers\CertificationOffer\CertificationOfferAttributeMapper;
use App\Core\Domain\Catalog\Models\CertificationOffer;

final class CreateCertificationOfferAction
{
    public function __construct(
        private readonly CertificationOfferAttributeMapper $attributeMapper,
    ) {}

    public function execute(CertificationOfferDto $dto): CertificationOffer
    {
        $certificationOffer = new CertificationOffer;
        $this->attributeMapper->apply($certificationOffer, $dto, isCreate: true);
        $certificationOffer->save();

        return $certificationOffer->refresh();
    }
}
