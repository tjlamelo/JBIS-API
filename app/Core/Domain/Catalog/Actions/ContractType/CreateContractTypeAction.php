<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\ContractType;

use App\Core\Domain\Catalog\DTOs\ContractType\ContractTypeDto;
use App\Core\Domain\Catalog\Mappers\ContractType\ContractTypeAttributeMapper;
use App\Core\Domain\Catalog\Models\ContractType;

final class CreateContractTypeAction
{
    public function __construct(
        private readonly ContractTypeAttributeMapper $attributeMapper,
    ) {}

    public function execute(ContractTypeDto $dto): ContractType
    {
        $contractType = new ContractType;
        $this->attributeMapper->apply($contractType, $dto, isCreate: true);
        $contractType->save();

        return $contractType->refresh();
    }
}
