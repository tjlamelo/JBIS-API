<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\WorkSchedule;

use App\Core\Domain\Catalog\Models\WorkSchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteWorkScheduleAction
{
    public function execute(int $workScheduleId): bool
    {
        /** @var WorkSchedule|null $schedule */
        $schedule = WorkSchedule::query()->find($workScheduleId);

        if (! $schedule) {
            throw new ModelNotFoundException("WorkSchedule {$workScheduleId} not found.");
        }

        return (bool) $schedule->delete();
    }
}
