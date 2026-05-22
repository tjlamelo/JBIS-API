<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\ContractType;

use App\Core\Domain\Catalog\Models\ContractType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteContractTypeAction
{
    public function execute(int $contractTypeId): bool
    {
        /** @var ContractType|null $contractType */
        $contractType = ContractType::query()->find($contractTypeId);

        if (! $contractType) {
            throw new ModelNotFoundException("ContractType {$contractTypeId} not found.");
        }

        return (bool) $contractType->delete();
    }
}
