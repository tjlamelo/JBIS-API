<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Company;

use App\Core\Domain\Catalog\DTOs\Company\CompanyDto;
use App\Core\Domain\Catalog\Mappers\Company\CompanyAttributeMapper;
use App\Core\Domain\Catalog\Models\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateCompanyAction
{
    public function __construct(
        private readonly CompanyAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $companyId, CompanyDto $dto): Company
    {
        /** @var Company|null $company */
        $company = Company::query()->find($companyId);

        if (! $company) {
            throw new ModelNotFoundException("Company {$companyId} not found.");
        }

        $this->attributeMapper->apply($company, $dto, isCreate: false);
        $company->save();

        return $company->refresh();
    }
}
