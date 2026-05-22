<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Agency;

use App\Core\Domain\Catalog\DTOs\Agency\AgencyDto;
use App\Core\Domain\Catalog\Models\Agency;

final class AgencyAttributeMapper
{
    public function apply(Agency $agency, AgencyDto $dto, bool $isCreate): void
    {
        if ($isCreate || $dto->has('name')) {
            if ($dto->name !== []) {
                $agency->setTranslations('name', $dto->name);
            }
        }

        if ($isCreate || $dto->has('slug')) {
            $agency->slug = $dto->slug;
        }

        if ($isCreate || $dto->has('description')) {
            if ($dto->description !== null && $dto->description !== []) {
                $agency->setTranslations('description', $dto->description);
            }
        }

        if ($isCreate || $dto->has('country_id')) {
            $agency->country_id = $dto->country_id;
        }

        if ($isCreate || $dto->has('city_id')) {
            $agency->city_id = $dto->city_id;
        }

        if ($isCreate || $dto->has('address')) {
            $agency->address = $dto->address;
        }

        if ($isCreate || $dto->has('latitude')) {
            $agency->latitude = $dto->latitude;
        }

        if ($isCreate || $dto->has('longitude')) {
            $agency->longitude = $dto->longitude;
        }

        if ($isCreate || $dto->has('phones')) {
            $agency->phones = $dto->phones;
        }

        if ($isCreate || $dto->has('whatsapp_numbers')) {
            $agency->whatsapp_numbers = $dto->whatsapp_numbers;
        }

        if ($isCreate || $dto->has('email')) {
            $agency->email = $dto->email;
        }

        if ($isCreate || $dto->has('manager_id')) {
            $agency->manager_id = $dto->manager_id;
        }

        if ($isCreate || $dto->has('number_of_employees')) {
            $agency->number_of_employees = $dto->number_of_employees;
        }

        if ($isCreate || $dto->has('opening_hours')) {
            $agency->opening_hours = $dto->opening_hours;
        }

        if ($isCreate || $dto->has('image_url')) {
            $agency->image_url = $dto->image_url;
        }

        if ($isCreate || $dto->has('is_active')) {
            $agency->is_active = $dto->is_active;
        }
    }
}
