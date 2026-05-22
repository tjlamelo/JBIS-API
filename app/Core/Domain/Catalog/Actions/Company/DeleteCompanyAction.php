<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Company;

use App\Core\Domain\Catalog\Models\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteCompanyAction
{
    public function execute(int $companyId): bool
    {
        /** @var Company|null $company */
        $company = Company::query()->find($companyId);

        if (! $company) {
            throw new ModelNotFoundException("Company {$companyId} not found.");
        }

        return (bool) $company->delete();
    }
}
