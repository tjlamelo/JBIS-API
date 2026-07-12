<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Services;

use App\Core\Application\Mail\Jobs\SendOperationsAlertMailJob;
use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Services\InAppNotificationService;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\Meeting;
use Illuminate\Support\Collection;

final class OperationsNotificationService
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function managementUsers(): Collection
    {
        return User::query()
            ->role(ApplicationRole::STAFF_ROLES)
            ->whereNotNull('email_verified_at')
            ->get(['id', 'name', 'email']);
    }

    public function meetingCreated(Meeting $meeting): void
    {
        $meeting->loadMissing(['organizer:id,name,email', 'attendees:id,name,email']);
        $organizerName = $meeting->organizer?->name ?? 'JBIS';
        $when = $meeting->scheduled_at?->timezone('Africa/Douala')->format('d/m/Y H:i') ?? '—';

        foreach ($meeting->attendees as $attendee) {
            if ($attendee->id === $meeting->organizer_id) {
                continue;
            }

            $title = __('notifications.ops_meeting_invite.title');
            $body = __('notifications.ops_meeting_invite.body', [
                'title' => $meeting->title,
                'when' => $when,
                'organizer' => $organizerName,
            ]);

            $this->push(
                $attendee,
                InAppNotificationType::OpsMeetingInvite,
                $title,
                $body,
                ['meeting_id' => $meeting->id],
                "ops_meeting_invite:{$meeting->id}:{$attendee->id}",
                '/admin/meetings',
            );
        }
    }

    public function attendanceMarkedPresent(Meeting $meeting, User $attendee): void
    {
        $title = __('notifications.ops_meeting_present.title');
        $body = __('notifications.ops_meeting_present.body', ['title' => $meeting->title]);

        $this->push(
            $attendee,
            InAppNotificationType::OpsMeetingPresent,
            $title,
            $body,
            ['meeting_id' => $meeting->id],
            "ops_meeting_present:{$meeting->id}:{$attendee->id}:".now()->toDateString(),
            '/admin/meetings',
        );
    }

    public function taskAssigned(AssignedTask $task): void
    {
        $task->loadMissing(['assignees:id,name,email', 'creator:id,name']);
        $creator = $task->creator?->name ?? 'JBIS';
        $due = $task->due_date?->format('d/m/Y') ?? '—';

        foreach ($task->assignees as $assignee) {
            $title = __('notifications.ops_task_assigned.title');
            $body = __('notifications.ops_task_assigned.body', [
                'title' => $task->title,
                'due' => $due,
                'by' => $creator,
            ]);

            $this->push(
                $assignee,
                InAppNotificationType::OpsTaskAssigned,
                $title,
                $body,
                ['assigned_task_id' => $task->id],
                "ops_task_assigned:{$task->id}:{$assignee->id}",
                '/admin/tasks',
            );
        }
    }

    public function taskCompleted(AssignedTask $task, User $actor): void
    {
        $task->loadMissing(['assignees:id,name,email']);
        $title = __('notifications.ops_task_completed.title');
        $body = __('notifications.ops_task_completed.body', [
            'title' => $task->title,
            'by' => $actor->name,
        ]);

        $recipients = $this->managementUsers()
            ->merge($task->assignees)
            ->unique('id')
            ->reject(fn (User $u) => $u->id === $actor->id);

        foreach ($recipients as $user) {
            $this->push(
                $user,
                InAppNotificationType::OpsTaskCompleted,
                $title,
                $body,
                ['assigned_task_id' => $task->id, 'actor_id' => $actor->id],
                "ops_task_completed:{$task->id}:{$user->id}:".now()->format('YmdHi'),
                '/admin/tasks',
            );
        }
    }

    public function taskOverdue(AssignedTask $task, User $assignee): void
    {
        $title = __('notifications.ops_task_overdue.title');
        $body = __('notifications.ops_task_overdue.body', [
            'title' => $task->title,
            'due' => $task->due_date?->format('d/m/Y') ?? '—',
        ]);

        $this->push(
            $assignee,
            InAppNotificationType::OpsTaskOverdue,
            $title,
            $body,
            ['assigned_task_id' => $task->id],
            "ops_task_overdue:{$task->id}:{$assignee->id}:".now()->toDateString(),
            '/admin/tasks',
        );
    }

    public function taskNotSubmitted(AssignedTask $task, User $assignee): void
    {
        $title = __('notifications.ops_task_not_submitted.title');
        $body = __('notifications.ops_task_not_submitted.body', ['title' => $task->title]);

        $this->push(
            $assignee,
            InAppNotificationType::OpsTaskNotSubmitted,
            $title,
            $body,
            ['assigned_task_id' => $task->id],
            "ops_task_not_submitted:{$task->id}:{$assignee->id}:".now()->toDateString(),
            '/admin/tasks',
        );
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function personalWeeklyRecap(User $user, array $summary): void
    {
        $title = __('notifications.ops_weekly_personal.title');
        $body = __('notifications.ops_weekly_personal.body', [
            'done' => (int) ($summary['done'] ?? 0),
            'open' => (int) ($summary['open'] ?? 0),
            'overdue' => (int) ($summary['overdue'] ?? 0),
            'hours' => number_format((float) ($summary['hours'] ?? 0), 1),
        ]);

        $this->push(
            $user,
            InAppNotificationType::OpsWeeklyPersonal,
            $title,
            $body,
            $summary,
            'ops_weekly_personal:'.$user->id.':'.now()->toDateString(),
            '/admin/tasks?view=week',
        );
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function managementWeeklyRecap(User $manager, array $summary): void
    {
        $title = __('notifications.ops_weekly_management.title');
        $body = __('notifications.ops_weekly_management.body', [
            'staff' => (int) ($summary['staff_count'] ?? 0),
            'done' => (int) ($summary['done'] ?? 0),
            'open' => (int) ($summary['open'] ?? 0),
            'overdue' => (int) ($summary['overdue'] ?? 0),
        ]);

        $this->push(
            $manager,
            InAppNotificationType::OpsWeeklyManagement,
            $title,
            $body,
            $summary,
            'ops_weekly_management:'.$manager->id.':'.now()->toDateString(),
            '/admin/tasks?view=week',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function push(
        User $user,
        InAppNotificationType $type,
        string $title,
        string $body,
        array $data,
        string $dedupeKey,
        string $actionUrl,
    ): void {
        $this->notifications->notify($user, $type, $title, $body, $data, $dedupeKey, $actionUrl);

        if ($user->email) {
            SendOperationsAlertMailJob::dispatch(
                userId: $user->id,
                subject: $title,
                body: $body,
                actionUrl: $actionUrl,
            )->onQueue('mail');
        }
    }
}
