<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Company;

use App\Core\Domain\Catalog\DTOs\Company\CompanyDto;
use App\Core\Domain\Catalog\Models\Company;

final class CompanyAttributeMapper
{
    public function apply(Company $company, CompanyDto $dto, bool $isCreate): void
    {
        if ($isCreate || $dto->has('name')) {
            $company->name = $dto->name;
        }

        if ($isCreate || $dto->has('slug')) {
            $company->slug = $dto->slug;
        }

        if ($isCreate || $dto->has('offer_category_id')) {
            $company->offer_category_id = $dto->offer_category_id;
        }

        if ($isCreate || $dto->has('country_id')) {
            $company->country_id = $dto->country_id;
        }

        if ($isCreate || $dto->has('city_id')) {
            $company->city_id = $dto->city_id;
        }

        if ($isCreate || $dto->has('address')) {
            $company->address = $dto->address;
        }

        if ($isCreate || $dto->has('type')) {
            $company->type = $dto->type;
        }

        if ($isCreate || $dto->has('status')) {
            $company->status = $dto->status;
        }

        if ($isCreate || $dto->has('email')) {
            $company->email = $dto->email;
        }

        if ($isCreate || $dto->has('phone')) {
            $company->phone = $dto->phone;
        }

        if ($isCreate || $dto->has('website')) {
            $company->website = $dto->website;
        }

        if ($isCreate || $dto->has('description')) {
            $company->description = $dto->description;
        }

        if ($isCreate || $dto->has('logo')) {
            $company->logo = $dto->logo;
        }

        if ($isCreate || $dto->has('is_approved')) {
            $company->is_approved = $dto->is_approved;
        }

        if ($isCreate || $dto->has('approved_by')) {
            $company->approved_by = $dto->approved_by;
        }
    }
}
