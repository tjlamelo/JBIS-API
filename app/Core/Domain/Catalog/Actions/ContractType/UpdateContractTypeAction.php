<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\ContractType;

use App\Core\Domain\Catalog\DTOs\ContractType\ContractTypeDto;
use App\Core\Domain\Catalog\Mappers\ContractType\ContractTypeAttributeMapper;
use App\Core\Domain\Catalog\Models\ContractType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateContractTypeAction
{
    public function __construct(
        private readonly ContractTypeAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $contractTypeId, ContractTypeDto $dto): ContractType
    {
        /** @var ContractType|null $contractType */
        $contractType = ContractType::query()->find($contractTypeId);

        if (! $contractType) {
            throw new ModelNotFoundException("ContractType {$contractTypeId} not found.");
        }

        $this->attributeMapper->apply($contractType, $dto, isCreate: false);
        $contractType->save();

        return $contractType->refresh();
    }
}
