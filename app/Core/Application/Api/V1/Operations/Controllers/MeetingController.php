<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\AssignedTaskResource;
use App\Core\Application\Api\V1\Operations\Resources\MeetingResource;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Enums\AssignedTaskPriority;
use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use App\Core\Domain\Operations\Enums\MeetingStatus;
use App\Core\Domain\Operations\Enums\MeetingType;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\DailyTask;
use App\Core\Domain\Operations\Models\Meeting;
use App\Core\Domain\Operations\Services\OperationsNotificationService;
use App\Core\Domain\Operations\Support\StaffUserResolver;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class MeetingController extends Controller
{
    public function __construct(
        private readonly OperationsNotificationService $opsNotifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $items = Meeting::query()
            ->with(['organizer:id,name,email'])
            ->withCount('assignedTasks')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), function ($q) use ($request): void {
                $term = '%'.trim((string) $request->query('q')).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)->orWhere('location', 'like', $term);
                });
            })
            ->orderByDesc('scheduled_at')
            ->paginate(max(1, min(100, (int) $request->integer('per_page', 20))));

        return BaseResponse::ok([
            'meetings' => MeetingResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Meeting::class);
        $user = $request->user();
        abort_if($user === null, 401);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(MeetingType::values())],
            'location' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'agenda' => ['nullable', 'string'],
            'minutes' => ['nullable', 'string'],
            'decisions' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(MeetingStatus::values())],
            'organizer_id' => ['nullable', 'integer', 'exists:users,id'],
            'attendee_ids' => ['sometimes', 'array'],
            'attendee_ids.*' => ['integer', 'exists:users,id'],
            'attendance' => ['sometimes', 'array'],
            'attendance.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance.*.is_present' => ['required', 'boolean'],
            'attendance.*.excuse_reason' => ['nullable', 'string', 'max:255'],
            'tasks' => ['sometimes', 'array'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.due_date' => ['nullable', 'date'],
            'tasks.*.estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'tasks.*.priority' => ['nullable', Rule::in(AssignedTaskPriority::values())],
            'tasks.*.assignee_ids' => ['sometimes', 'array'],
            'tasks.*.assignee_ids.*' => ['integer', 'exists:users,id'],
            'tasks.*.week_start_date' => ['nullable', 'date'],
        ]);

        $organizerId = (int) ($data['organizer_id'] ?? $user->id);
        StaffUserResolver::assertStaffUserId($organizerId);

        $meeting = DB::transaction(function () use ($data, $organizerId, $user): Meeting {
            $meeting = Meeting::query()->create([
                'title' => $data['title'],
                'type' => $data['type'],
                'location' => $data['location'] ?? null,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'agenda' => $data['agenda'] ?? null,
                'minutes' => $data['minutes'] ?? null,
                'decisions' => $data['decisions'] ?? null,
                'organizer_id' => $organizerId,
                'status' => $data['status'] ?? MeetingStatus::Scheduled->value,
            ]);

            $attendeeIds = StaffUserResolver::filterStaffIds(
                array_values(array_unique(array_map('intval', $data['attendee_ids'] ?? [])))
            );
            if (! in_array($organizerId, $attendeeIds, true)) {
                $attendeeIds[] = $organizerId;
            }

            $sync = [];
            foreach ($attendeeIds as $id) {
                $sync[$id] = ['is_present' => false, 'excuse_reason' => null];
            }
            $meeting->attendees()->sync($sync);

            if (! empty($data['attendance'])) {
                foreach ($data['attendance'] as $row) {
                    $uid = (int) $row['user_id'];
                    if (! isset($sync[$uid])) {
                        continue;
                    }
                    $meeting->attendees()->updateExistingPivot($uid, [
                        'is_present' => (bool) $row['is_present'],
                        'excuse_reason' => $row['excuse_reason'] ?? null,
                    ]);
                }
            }

            foreach ($data['tasks'] ?? [] as $taskRow) {
                $assigneeIds = StaffUserResolver::filterStaffIds(
                    array_values(array_unique(array_map('intval', $taskRow['assignee_ids'] ?? [])))
                );
                $task = AssignedTask::query()->create([
                    'title' => $taskRow['title'],
                    'description' => $taskRow['description'] ?? null,
                    'meeting_id' => $meeting->id,
                    'due_date' => $taskRow['due_date'] ?? null,
                    'estimated_minutes' => $taskRow['estimated_minutes'] ?? null,
                    'week_start_date' => $taskRow['week_start_date']
                        ?? Carbon::parse($meeting->scheduled_at)->startOfWeek(Carbon::MONDAY)->toDateString(),
                    'priority' => $taskRow['priority'] ?? AssignedTaskPriority::Medium->value,
                    'status' => AssignedTaskStatus::Todo->value,
                    'created_by' => $user->id,
                ]);
                if ($assigneeIds !== []) {
                    $task->assignees()->sync($assigneeIds);
                }
            }

            return $meeting;
        });

        $meeting->load(['organizer:id,name,email', 'attendees:id,name,email', 'assignedTasks.assignees:id,name,email']);
        $this->opsNotifications->meetingCreated($meeting);
        foreach ($meeting->assignedTasks as $task) {
            $this->opsNotifications->taskAssigned($task);
        }

        $presentIds = collect($data['attendance'] ?? [])
            ->filter(fn ($row) => (bool) ($row['is_present'] ?? false))
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id);
        foreach ($presentIds as $presentId) {
            $attendee = User::query()->find($presentId);
            if ($attendee) {
                $this->opsNotifications->attendanceMarkedPresent($meeting, $attendee);
            }
        }

        return BaseResponse::created([
            'message' => __('Réunion créée.'),
            'meeting' => new MeetingResource($meeting),
        ])->toJsonResponse();
    }

    public function show(Meeting $meeting): JsonResponse
    {
        $this->authorize('view', $meeting);
        $meeting->load(['organizer:id,name,email', 'attendees:id,name,email', 'assignedTasks.assignees:id,name,email']);

        return BaseResponse::ok([
            'meeting' => new MeetingResource($meeting),
        ])->toJsonResponse();
    }

    public function update(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorize('update', $meeting);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(MeetingType::values())],
            'location' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'agenda' => ['nullable', 'string'],
            'minutes' => ['nullable', 'string'],
            'decisions' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(MeetingStatus::values())],
            'organizer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'attendee_ids' => ['sometimes', 'array'],
            'attendee_ids.*' => ['integer', 'exists:users,id'],
            'attendance' => ['sometimes', 'array'],
            'attendance.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance.*.is_present' => ['required', 'boolean'],
            'attendance.*.excuse_reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (array_key_exists('organizer_id', $data)) {
            StaffUserResolver::assertStaffUserId((int) $data['organizer_id']);
        }

        $newlyPresent = [];

        DB::transaction(function () use ($meeting, $data, &$newlyPresent): void {
            $meeting->fill(collect($data)->except(['attendee_ids', 'attendance'])->all());
            $meeting->save();

            if (array_key_exists('attendee_ids', $data)) {
                $attendeeIds = StaffUserResolver::filterStaffIds(
                    array_values(array_unique(array_map('intval', $data['attendee_ids'])))
                );
                $existing = $meeting->attendees()->get()->keyBy('id');
                $sync = [];
                foreach ($attendeeIds as $id) {
                    $sync[$id] = [
                        'is_present' => (bool) ($existing->get($id)?->pivot?->is_present ?? false),
                        'excuse_reason' => $existing->get($id)?->pivot?->excuse_reason,
                    ];
                }
                $meeting->attendees()->sync($sync);
            }

            if (! empty($data['attendance'])) {
                foreach ($data['attendance'] as $row) {
                    $uid = (int) $row['user_id'];
                    $wasPresent = (bool) $meeting->attendees()->where('users.id', $uid)->first()?->pivot?->is_present;
                    $isPresent = (bool) $row['is_present'];
                    $meeting->attendees()->updateExistingPivot($uid, [
                        'is_present' => $isPresent,
                        'excuse_reason' => $row['excuse_reason'] ?? null,
                    ]);
                    if ($isPresent && ! $wasPresent) {
                        $newlyPresent[] = $uid;
                    }
                }
            }
        });

        $meeting->load(['organizer:id,name,email', 'attendees:id,name,email', 'assignedTasks.assignees:id,name,email']);

        foreach ($newlyPresent as $uid) {
            $attendee = User::query()->find($uid);
            if ($attendee) {
                $this->opsNotifications->attendanceMarkedPresent($meeting, $attendee);
            }
        }

        return BaseResponse::ok([
            'message' => __('Réunion mise à jour.'),
            'meeting' => new MeetingResource($meeting),
        ])->toJsonResponse();
    }

    public function destroy(Meeting $meeting): JsonResponse
    {
        $this->authorize('delete', $meeting);
        $meeting->delete();

        return BaseResponse::ok(['message' => __('Réunion supprimée.')])->toJsonResponse();
    }

    public function staffUsers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $users = User::query()
            ->role(ApplicationRole::STAFF_ROLES)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return BaseResponse::ok([
            'staff' => $users->map(static fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values(),
        ])->toJsonResponse();
    }

    public function weekBoard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Meeting::class);

        $weekStart = Carbon::parse((string) $request->input('week_start', Carbon::now('Africa/Douala')->toDateString()))
            ->timezone('Africa/Douala')
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $meetings = Meeting::query()
            ->with(['organizer:id,name,email', 'attendees:id,name,email'])
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->orderBy('scheduled_at')
            ->get();

        $tasks = AssignedTask::query()
            ->with(['assignees:id,name,email', 'meeting:id,title', 'creator:id,name'])
            ->where(function ($q) use ($weekStart, $weekEnd): void {
                $q->whereBetween('due_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhereBetween('week_start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhere(function ($inner) use ($weekStart, $weekEnd): void {
                        $inner->whereNull('due_date')
                            ->whereNull('week_start_date')
                            ->whereBetween('created_at', [$weekStart, $weekEnd]);
                    });
            })
            ->orderByRaw("CASE priority WHEN 'URGENT' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->get();

        $daily = DailyTask::query()
            ->with(['user:id,name,email', 'assignedTask:id,title'])
            ->whereBetween('task_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('task_date')
            ->get();

        $days = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $date = $d->toDateString();
            $days[] = [
                'date' => $date,
                'weekday' => $d->locale(app()->getLocale())->isoFormat('dddd'),
                'meetings' => MeetingResource::collection(
                    $meetings->filter(fn (Meeting $m) => $m->scheduled_at?->toDateString() === $date)->values()
                ),
                'assigned_tasks' => AssignedTaskResource::collection(
                    $tasks->filter(function (AssignedTask $t) use ($date): bool {
                        return $t->due_date?->toDateString() === $date
                            || $t->week_start_date?->toDateString() === $date;
                    })->values()
                ),
                'daily_tasks' => \App\Core\Application\Api\V1\Operations\Resources\DailyTaskResource::collection(
                    $daily->filter(fn (DailyTask $t) => $t->task_date?->toDateString() === $date)->values()
                ),
            ];
        }

        $summary = [
            'meetings' => $meetings->count(),
            'assigned_total' => $tasks->count(),
            'assigned_done' => $tasks->filter(fn (AssignedTask $t) => $t->status === AssignedTaskStatus::Done)->count(),
            'assigned_open' => $tasks->filter(fn (AssignedTask $t) => in_array($t->status, [
                AssignedTaskStatus::Todo,
                AssignedTaskStatus::InProgress,
            ], true))->count(),
            'assigned_overdue' => $tasks->filter(fn (AssignedTask $t) => $t->isOverdue())->count(),
            'daily_total' => $daily->count(),
            'minutes_logged' => $daily->sum(fn (DailyTask $t) => $t->totalMinutes()),
        ];

        return BaseResponse::ok([
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'summary' => $summary,
            'days' => $days,
            'assigned_tasks' => AssignedTaskResource::collection($tasks),
            'timeline' => $this->buildTimeline($meetings, $tasks, $daily),
        ])->toJsonResponse();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Meeting>  $meetings
     * @param  \Illuminate\Support\Collection<int, AssignedTask>  $tasks
     * @param  \Illuminate\Support\Collection<int, DailyTask>  $daily
     * @return list<array<string, mixed>>
     */
    private function buildTimeline($meetings, $tasks, $daily): array
    {
        $events = [];

        foreach ($meetings as $meeting) {
            $events[] = [
                'id' => 'meeting-'.$meeting->id,
                'kind' => 'meeting',
                'at' => $meeting->scheduled_at?->toIso8601String(),
                'title' => $meeting->title,
                'status' => $meeting->status?->value ?? $meeting->status,
                'meta' => [
                    'duration_minutes' => $meeting->duration_minutes,
                    'type' => $meeting->type?->value ?? $meeting->type,
                    'organizer' => $meeting->organizer?->name,
                ],
                'ref_id' => $meeting->id,
            ];
        }

        foreach ($tasks as $task) {
            $events[] = [
                'id' => 'task-'.$task->id,
                'kind' => 'assigned_task',
                'at' => ($task->due_date?->toDateString() ?? $task->week_start_date?->toDateString() ?? $task->created_at?->toDateString()).'T09:00:00+01:00',
                'title' => $task->title,
                'status' => $task->status?->value ?? $task->status,
                'meta' => [
                    'priority' => $task->priority?->value ?? $task->priority,
                    'progress_percentage' => $task->progress_percentage,
                    'estimated_minutes' => $task->estimated_minutes,
                    'minutes_spent' => $task->minutes_spent,
                    'is_overdue' => $task->isOverdue(),
                    'assignees' => $task->assignees->pluck('name')->values(),
                ],
                'ref_id' => $task->id,
            ];
        }

        foreach ($daily as $log) {
            $events[] = [
                'id' => 'daily-'.$log->id,
                'kind' => 'daily_task',
                'at' => $log->task_date?->toDateString().'T18:00:00+01:00',
                'title' => $log->title,
                'status' => $log->status?->value ?? $log->status,
                'meta' => [
                    'total_minutes' => $log->totalMinutes(),
                    'user' => $log->user?->name,
                    'is_outside_meeting' => (bool) $log->is_outside_meeting,
                ],
                'ref_id' => $log->id,
            ];
        }

        usort($events, static fn ($a, $b) => strcmp((string) $a['at'], (string) $b['at']));

        return $events;
    }
}
