<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Benefit;

use App\Core\Domain\Catalog\DTOs\Benefit\BenefitDto;
use App\Core\Domain\Catalog\Mappers\Benefit\BenefitAttributeMapper;
use App\Core\Domain\Catalog\Models\Benefit;

final class CreateBenefitAction
{
    public function __construct(
        private readonly BenefitAttributeMapper $attributeMapper,
    ) {}

    public function execute(BenefitDto $dto): Benefit
    {
        $benefit = new Benefit;
        $this->attributeMapper->apply($benefit, $dto, isCreate: true);
        $benefit->save();

        return $benefit->refresh();
    }
}
