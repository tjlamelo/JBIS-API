<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Support;

use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\DailyTask;

final class AssignedTaskTimeTracker
{
    public static function refreshMinutesSpent(AssignedTask $task): void
    {
        $minutes = (int) DailyTask::query()
            ->where('assigned_task_id', $task->id)
            ->get()
            ->sum(static function (DailyTask $daily): int {
                if ($daily->minutes_spent !== null && (int) $daily->minutes_spent > 0) {
                    return (int) $daily->minutes_spent;
                }

                return ((int) ($daily->hours_spent ?? 0)) * 60;
            });

        $task->forceFill(['minutes_spent' => $minutes])->saveQuietly();
    }
}
