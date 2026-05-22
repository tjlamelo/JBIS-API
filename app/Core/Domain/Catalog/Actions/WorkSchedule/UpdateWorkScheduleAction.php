<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\WorkSchedule;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\WorkSchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateWorkScheduleAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(int $workScheduleId, CatalogNameSlugDto $dto): WorkSchedule
    {
        /** @var WorkSchedule|null $schedule */
        $schedule = WorkSchedule::query()->find($workScheduleId);

        if (! $schedule) {
            throw new ModelNotFoundException("WorkSchedule {$workScheduleId} not found.");
        }

        $this->nameSlugMapper->applyNameAndSlug($schedule, $dto->provided_keys, $dto->name, $dto->slug, false);
        $schedule->save();

        return $schedule->refresh();
    }
}
