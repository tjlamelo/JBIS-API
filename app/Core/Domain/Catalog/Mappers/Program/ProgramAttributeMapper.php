<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Program;

use App\Core\Domain\Catalog\DTOs\Program\ProgramDto;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\States\ProgramStatus;

final class ProgramAttributeMapper
{
    public function apply(Program $program, ProgramDto $dto, bool $isCreate): void
    {
        if ($this->hasKey($dto, 'name') && $dto->name !== []) {
            $program->setTranslations('name', $dto->name);
        }

        if ($this->hasKey($dto, 'description') && $dto->description !== null) {
            $program->setTranslations('description', $dto->description);
        }

        if ($this->hasKey($dto, 'slug') && $dto->slug !== null && $dto->slug !== []) {
            $program->setTranslations('slug', $dto->slug);
        }

        if ($this->hasKey($dto, 'geographic_zone_id')) {
            $program->geographic_zone_id = $dto->geographic_zone_id;
        }

        if ($this->hasKey($dto, 'procedure_duration')) {
            $program->procedure_duration = $dto->procedure_duration;
        }

        if ($this->hasKey($dto, 'duration_unit')) {
            $program->duration_unit = $dto->duration_unit;
        }

        if ($this->hasKey($dto, 'age_min')) {
            $program->age_min = $dto->age_min;
        }

        if ($this->hasKey($dto, 'age_max')) {
            $program->age_max = $dto->age_max;
        }

        if ($this->hasKey($dto, 'is_featured')) {
            $program->is_featured = $dto->is_featured;
        }

        if ($this->hasKey($dto, 'is_urgent')) {
            $program->is_urgent = $dto->is_urgent;
        }

        if ($this->hasKey($dto, 'views_count')) {
            $program->views_count = max(0, $dto->views_count);
        }

        if ($this->hasKey($dto, 'image_media')) {
            $program->image_media = $dto->image_media;
        }

        if ($this->hasKey($dto, 'status')) {
            $program->status = $dto->status;
        }

        if ($this->hasKey($dto, 'start_date')) {
            $program->start_date = $dto->start_date;
        }

        if ($this->hasKey($dto, 'end_date')) {
            $program->end_date = $dto->end_date;
        }

        $this->applyPublishedAt($program, $dto, $isCreate);

        if ($isCreate && $dto->user_id !== null) {
            $program->user_id = $dto->user_id;
        }
    }

    private function applyPublishedAt(Program $program, ProgramDto $dto, bool $isCreate): void
    {
        if ($this->hasKey($dto, 'published_at')) {
            $pub = $dto->published_at;
            $status = (string) ($program->status ?? ProgramStatus::Published->value);

            if ($pub !== null && $pub !== '') {
                $program->published_at = $pub;
            } else {
                $program->published_at = $status === ProgramStatus::Published->value ? now() : null;
            }

            return;
        }

        if ($isCreate) {
            $program->published_at = $program->status === ProgramStatus::Published->value ? now() : null;

            return;
        }

        if (
            $program->status === ProgramStatus::Published->value
            && $program->published_at === null
        ) {
            $program->published_at = now();
        }
    }

    private function hasKey(ProgramDto $dto, string $key): bool
    {
        return in_array($key, $dto->provided_keys, true);
    }
}
