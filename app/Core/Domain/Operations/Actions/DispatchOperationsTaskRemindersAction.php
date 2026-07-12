<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Actions;

use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Services\OperationsNotificationService;
use Illuminate\Support\Carbon;

final class DispatchOperationsTaskRemindersAction
{
    public function __construct(
        private readonly OperationsNotificationService $notifications,
    ) {}

    public function execute(?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::now('Africa/Douala'))->startOfDay();
        $overdueCount = 0;
        $notSubmittedCount = 0;

        $openTasks = AssignedTask::query()
            ->with(['assignees:id,name,email', 'dailyTasks' => fn ($q) => $q->whereDate('task_date', '>=', $today->copy()->startOfWeek()->toDateString())])
            ->whereIn('status', [AssignedTaskStatus::Todo->value, AssignedTaskStatus::InProgress->value])
            ->get();

        foreach ($openTasks as $task) {
            $isOverdue = $task->due_date !== null && $task->due_date->lt($today);
            foreach ($task->assignees as $assignee) {
                if ($isOverdue) {
                    $this->notifications->taskOverdue($task, $assignee);
                    $overdueCount++;
                }

                $hasLog = $task->dailyTasks->contains('user_id', $assignee->id);
                if (! $hasLog && $task->status === AssignedTaskStatus::InProgress) {
                    $this->notifications->taskNotSubmitted($task, $assignee);
                    $notSubmittedCount++;
                }
            }
        }

        return [
            'overdue_notified' => $overdueCount,
            'not_submitted_notified' => $notSubmittedCount,
        ];
    }
}
