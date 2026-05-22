<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Benefit;

use App\Core\Domain\Catalog\DTOs\Benefit\BenefitDto;
use App\Core\Domain\Catalog\Mappers\Benefit\BenefitAttributeMapper;
use App\Core\Domain\Catalog\Models\Benefit;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateBenefitAction
{
    public function __construct(
        private readonly BenefitAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $benefitId, BenefitDto $dto): Benefit
    {
        /** @var Benefit|null $benefit */
        $benefit = Benefit::query()->find($benefitId);

        if (! $benefit) {
            throw new ModelNotFoundException("Benefit {$benefitId} not found.");
        }

        $this->attributeMapper->apply($benefit, $dto, isCreate: false);
        $benefit->save();

        return $benefit->refresh();
    }
}
