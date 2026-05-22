<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\CertificationOffer;

use App\Core\Domain\Catalog\DTOs\CertificationOffer\CertificationOfferDto;
use App\Core\Domain\Catalog\Models\CertificationOffer;

final class CertificationOfferAttributeMapper
{
    public function apply(CertificationOffer $certificationOffer, CertificationOfferDto $dto, bool $isCreate): void
    {
        if ($isCreate || $this->hasKey($dto, 'domain')) {
            $certificationOffer->domain = $dto->domain;
        }

        if ($isCreate || $this->hasKey($dto, 'title')) {
            $certificationOffer->title = $dto->title;
        }

        if ($isCreate || $this->hasKey($dto, 'organization')) {
            $certificationOffer->organization = $dto->organization;
        }

        if ($isCreate || $this->hasKey($dto, 'description')) {
            $certificationOffer->description = $dto->description;
        }

        if ($isCreate || $this->hasKey($dto, 'cost')) {
            $certificationOffer->cost = $dto->cost;
        }

        if ($isCreate || $this->hasKey($dto, 'currency')) {
            $certificationOffer->currency = $dto->currency;
        }

        if ($isCreate || $this->hasKey($dto, 'exam_mode')) {
            $certificationOffer->exam_mode = $dto->exam_mode;
        }

        if ($isCreate || $this->hasKey($dto, 'validity_years')) {
            $certificationOffer->validity_years = $dto->validity_years;
        }

        if ($isCreate || $this->hasKey($dto, 'level')) {
            $certificationOffer->level = $dto->level;
        }

        if ($isCreate || $this->hasKey($dto, 'process_flow_id')) {
            $certificationOffer->process_flow_id = $dto->process_flow_id;
        }

        if ($isCreate || $this->hasKey($dto, 'is_active')) {
            $certificationOffer->is_active = $dto->is_active;
        }
    }

    private function hasKey(CertificationOfferDto $dto, string $key): bool
    {
        return in_array($key, $dto->provided_keys, true);
    }
}
