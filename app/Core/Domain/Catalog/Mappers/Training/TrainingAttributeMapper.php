<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Training;

use App\Core\Domain\Catalog\DTOs\Training\TrainingDto;
use App\Core\Domain\Catalog\Models\Training;

final class TrainingAttributeMapper
{
    public function apply(Training $training, TrainingDto $dto, bool $isCreate): void
    {
        if ($isCreate || $this->hasKey($dto, 'domain')) {
            $training->domain = $dto->domain;
        }

        if ($isCreate || $this->hasKey($dto, 'title')) {
            $training->title = $dto->title;
        }

        if ($isCreate || $this->hasKey($dto, 'organization')) {
            $training->organization = $dto->organization;
        }

        if ($isCreate || $this->hasKey($dto, 'description')) {
            $training->description = $dto->description;
        }

        if ($isCreate || $this->hasKey($dto, 'start_date')) {
            $training->start_date = $dto->start_date;
        }

        if ($isCreate || $this->hasKey($dto, 'end_date')) {
            $training->end_date = $dto->end_date;
        }

        if ($isCreate || $this->hasKey($dto, 'duration_hours')) {
            $training->duration_hours = $dto->duration_hours;
        }

        if ($isCreate || $this->hasKey($dto, 'duration_days')) {
            $training->duration_days = $dto->duration_days;
        }

        if ($isCreate || $this->hasKey($dto, 'mode')) {
            $training->mode = $dto->mode;
        }

        if ($isCreate || $this->hasKey($dto, 'location')) {
            $training->location = $dto->location;
        }

        if ($isCreate || $this->hasKey($dto, 'prerequisites')) {
            $training->prerequisites = $dto->prerequisites;
        }

        if ($isCreate || $this->hasKey($dto, 'is_certified')) {
            $training->is_certified = $dto->is_certified;
        }

        if ($isCreate || $this->hasKey($dto, 'certificate_name')) {
            $training->certificate_name = $dto->certificate_name;
        }

        if ($isCreate || $this->hasKey($dto, 'is_active')) {
            $training->is_active = $dto->is_active;
        }
    }

    private function hasKey(TrainingDto $dto, string $key): bool
    {
        return in_array($key, $dto->provided_keys, true);
    }
}
