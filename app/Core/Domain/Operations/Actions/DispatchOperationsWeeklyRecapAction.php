<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\DailyTask;
use App\Core\Domain\Operations\Models\Meeting;
use App\Core\Domain\Operations\Services\OperationsNotificationService;
use Illuminate\Support\Carbon;

final class DispatchOperationsWeeklyRecapAction
{
    public function __construct(
        private readonly OperationsNotificationService $notifications,
    ) {}

    public function execute(?Carbon $weekEnd = null): array
    {
        $tz = 'Africa/Douala';
        $end = ($weekEnd ?? Carbon::now($tz))->endOfDay();
        $start = $end->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $staff = User::query()
            ->role(ApplicationRole::STAFF_ROLES)
            ->whereNotNull('email_verified_at')
            ->get(['id', 'name', 'email']);

        $tasks = AssignedTask::query()
            ->with(['assignees:id,name,email', 'dailyTasks'])
            ->where(function ($q) use ($start, $end): void {
                $q->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('week_start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($inner) use ($start, $end): void {
                        $inner->whereNull('due_date')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->get();

        $dailyHours = DailyTask::query()
            ->whereBetween('task_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');

        $teamDone = 0;
        $teamOpen = 0;
        $teamOverdue = 0;
        $perStaff = [];

        foreach ($staff as $user) {
            $userTasks = $tasks->filter(
                fn (AssignedTask $task) => $task->assignees->contains('id', $user->id)
            );

            $done = $userTasks->filter(fn (AssignedTask $t) => $t->status === AssignedTaskStatus::Done)->count();
            $open = $userTasks->filter(fn (AssignedTask $t) => in_array($t->status, [
                AssignedTaskStatus::Todo,
                AssignedTaskStatus::InProgress,
            ], true))->count();
            $overdue = $userTasks->filter(function (AssignedTask $t) use ($end): bool {
                if (in_array($t->status, [AssignedTaskStatus::Done, AssignedTaskStatus::Cancelled], true)) {
                    return false;
                }

                return $t->due_date !== null && $t->due_date->lt($end->toDateString());
            })->count();

            $hours = 0.0;
            foreach ($dailyHours->get($user->id, collect()) as $daily) {
                $minutes = (int) ($daily->minutes_spent ?? 0);
                if ($minutes > 0) {
                    $hours += $minutes / 60;
                } else {
                    $hours += (float) ($daily->hours_spent ?? 0);
                }
            }

            $teamDone += $done;
            $teamOpen += $open;
            $teamOverdue += $overdue;

            $summary = compact('done', 'open', 'overdue', 'hours');
            $perStaff[$user->id] = array_merge($summary, ['name' => $user->name]);
            $this->notifications->personalWeeklyRecap($user, $summary);
        }

        $managementSummary = [
            'staff_count' => $staff->count(),
            'done' => $teamDone,
            'open' => $teamOpen,
            'overdue' => $teamOverdue,
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'by_staff' => array_values($perStaff),
        ];

        foreach ($staff as $manager) {
            $this->notifications->managementWeeklyRecap($manager, $managementSummary);
        }

        $meetingsCount = Meeting::query()
            ->whereBetween('scheduled_at', [$start, $end])
            ->count();

        return [
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'staff_notified' => $staff->count(),
            'meetings' => $meetingsCount,
            'summary' => $managementSummary,
        ];
    }
}
