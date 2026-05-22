<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Agency;

use App\Core\Domain\Catalog\Models\Agency;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteAgencyAction
{
    public function execute(int $agencyId): bool
    {
        /** @var Agency|null $agency */
        $agency = Agency::query()->find($agencyId);

        if (! $agency) {
            throw new ModelNotFoundException("Agency {$agencyId} not found.");
        }

        return (bool) $agency->delete();
    }
}
