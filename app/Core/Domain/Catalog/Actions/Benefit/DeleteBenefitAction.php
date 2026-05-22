<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Benefit;

use App\Core\Domain\Catalog\Models\Benefit;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteBenefitAction
{
    public function execute(int $benefitId): bool
    {
        /** @var Benefit|null $benefit */
        $benefit = Benefit::query()->find($benefitId);

        if (! $benefit) {
            throw new ModelNotFoundException("Benefit {$benefitId} not found.");
        }

        return (bool) $benefit->delete();
    }
}
