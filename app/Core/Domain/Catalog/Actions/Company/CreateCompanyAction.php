<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Company;

use App\Core\Domain\Catalog\DTOs\Company\CompanyDto;
use App\Core\Domain\Catalog\Mappers\Company\CompanyAttributeMapper;
use App\Core\Domain\Catalog\Models\Company;

final class CreateCompanyAction
{
    public function __construct(
        private readonly CompanyAttributeMapper $attributeMapper,
    ) {}

    public function execute(CompanyDto $dto): Company
    {
        $company = new Company;
        $this->attributeMapper->apply($company, $dto, isCreate: true);
        $company->save();

        return $company->refresh();
    }
}
