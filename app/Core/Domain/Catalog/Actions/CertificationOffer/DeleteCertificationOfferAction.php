<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\CertificationOffer;

use App\Core\Domain\Catalog\Models\CertificationOffer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteCertificationOfferAction
{
    public function execute(int $certificationOfferId): bool
    {
        /** @var CertificationOffer|null $certificationOffer */
        $certificationOffer = CertificationOffer::query()->find($certificationOfferId);

        if (! $certificationOffer) {
            throw new ModelNotFoundException("CertificationOffer {$certificationOfferId} not found.");
        }

        return (bool) $certificationOffer->delete();
    }
}
