<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\WorkSchedule;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\WorkSchedule;

final class CreateWorkScheduleAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(CatalogNameSlugDto $dto): WorkSchedule
    {
        $schedule = new WorkSchedule;
        $this->nameSlugMapper->applyNameAndSlug($schedule, $dto->provided_keys, $dto->name, $dto->slug, true);
        $schedule->save();

        return $schedule->refresh();
    }
}
