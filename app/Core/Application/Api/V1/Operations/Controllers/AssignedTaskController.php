<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\AssignedTaskResource;
use App\Core\Domain\Operations\Enums\AssignedTaskPriority;
use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\Meeting;
use App\Core\Domain\Operations\Services\OperationsNotificationService;
use App\Core\Domain\Operations\Support\OperationsAccess;
use App\Core\Domain\Operations\Support\StaffUserResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AssignedTaskController extends Controller
{
    public function __construct(
        private readonly OperationsNotificationService $opsNotifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AssignedTask::class);

        $items = AssignedTask::query()
            ->with(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email'])
            ->when(! OperationsAccess::canViewAllTasks($request->user()), function ($q) use ($request): void {
                $uid = (int) $request->user()->id;
                $q->where(function ($inner) use ($uid): void {
                    $inner->where('created_by', $uid)
                        ->orWhereHas('assignees', fn ($a) => $a->where('users.id', $uid));
                });
            })
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('priority'), fn ($q, $p) => $q->where('priority', $p))
            ->when($request->query('meeting_id'), fn ($q, $id) => $q->where('meeting_id', (int) $id))
            ->when($request->query('week_start'), function ($q) use ($request): void {
                $start = Carbon::parse((string) $request->query('week_start'))->startOfWeek(Carbon::MONDAY);
                $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
                $q->where(function ($inner) use ($start, $end): void {
                    $inner->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                        ->orWhereBetween('week_start_date', [$start->toDateString(), $end->toDateString()]);
                });
            })
            ->when($request->query('assignee_id'), function ($q) use ($request): void {
                $assigneeId = (int) $request->query('assignee_id');
                $q->whereHas('assignees', fn ($inner) => $inner->where('users.id', $assigneeId));
            })
            ->when($request->query('overdue') === '1', function ($q): void {
                $q->whereDate('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', [AssignedTaskStatus::Done->value, AssignedTaskStatus::Cancelled->value]);
            })
            ->when($request->query('q'), function ($q) use ($request): void {
                $term = '%'.trim((string) $request->query('q')).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(max(1, min(100, (int) $request->integer('per_page', 20))));

        return BaseResponse::ok([
            'assigned_tasks' => AssignedTaskResource::collection($items->items()),
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
        $this->authorize('create', AssignedTask::class);
        $user = $request->user();
        abort_if($user === null, 401);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meeting_id' => ['nullable', 'integer', 'exists:meetings,id'],
            'due_date' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'week_start_date' => ['nullable', 'date'],
            'priority' => ['sometimes', Rule::in(AssignedTaskPriority::values())],
            'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', Rule::in(AssignedTaskStatus::values())],
            'notes' => ['nullable', 'string'],
            'assignee_ids' => ['sometimes', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $meeting = isset($data['meeting_id'])
            ? Meeting::query()->with('attendees')->find((int) $data['meeting_id'])
            : null;

        if (! StaffUserResolver::canManageMeetingTasks($user, $meeting)) {
            throw ValidationException::withMessages([
                'meeting_id' => [__('Vous devez être organisateur ou présent à la réunion pour y ajouter une tâche.')],
            ]);
        }

        $assigneeIds = StaffUserResolver::filterStaffIds(
            array_values(array_unique(array_map('intval', $data['assignee_ids'] ?? [])))
        );

        // Present staff (non-manager) can only assign to self unless manage_meetings.
        if ($meeting !== null && ! OperationsAccess::canAssignToOthers($user, $meeting)) {
            $assigneeIds = [$user->id];
        }

        if ($assigneeIds === []) {
            $assigneeIds = [$user->id];
        }

        $task = DB::transaction(function () use ($data, $user, $assigneeIds, $meeting): AssignedTask {
            $status = $data['status'] ?? AssignedTaskStatus::Todo->value;
            $task = AssignedTask::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'meeting_id' => $data['meeting_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'estimated_minutes' => $data['estimated_minutes'] ?? null,
                'week_start_date' => $data['week_start_date']
                    ?? ($meeting?->scheduled_at
                        ? Carbon::parse($meeting->scheduled_at)->startOfWeek(Carbon::MONDAY)->toDateString()
                        : Carbon::now('Africa/Douala')->startOfWeek(Carbon::MONDAY)->toDateString()),
                'priority' => $data['priority'] ?? AssignedTaskPriority::Medium->value,
                'progress_percentage' => $data['progress_percentage'] ?? 0,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'started_at' => $status === AssignedTaskStatus::InProgress->value ? now() : null,
                'completed_at' => $status === AssignedTaskStatus::Done->value ? now() : null,
            ]);

            $task->assignees()->sync($assigneeIds);

            return $task;
        });

        $task->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email']);
        $this->opsNotifications->taskAssigned($task);

        return BaseResponse::created([
            'message' => __('Tâche assignée créée.'),
            'assigned_task' => new AssignedTaskResource($task),
        ])->toJsonResponse();
    }

    public function show(AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('view', $assignedTask);
        $assignedTask->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email', 'dailyTasks', 'renewedFrom:id,title']);

        return BaseResponse::ok([
            'assigned_task' => new AssignedTaskResource($assignedTask),
        ])->toJsonResponse();
    }

    public function update(Request $request, AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('update', $assignedTask);
        $user = $request->user();
        abort_if($user === null, 401);

        $previousStatus = $assignedTask->status?->value ?? (string) $assignedTask->status;

        $canFullyEdit = OperationsAccess::canViewAllTasks($user)
            || OperationsAccess::canManageMeetings($user)
            || (int) $assignedTask->created_by === (int) $user->id;

        $data = $canFullyEdit
            ? $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'meeting_id' => ['nullable', 'integer', 'exists:meetings,id'],
                'due_date' => ['nullable', 'date'],
                'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
                'week_start_date' => ['nullable', 'date'],
                'priority' => ['sometimes', Rule::in(AssignedTaskPriority::values())],
                'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
                'status' => ['sometimes', Rule::in(AssignedTaskStatus::values())],
                'final_result' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
                'assignee_ids' => ['sometimes', 'array'],
                'assignee_ids.*' => ['integer', 'exists:users,id'],
            ])
            : $request->validate([
                'status' => ['sometimes', Rule::in(AssignedTaskStatus::values())],
                'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
                'final_result' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
            ]);

        if (! $canFullyEdit && array_key_exists('assignee_ids', $data)) {
            unset($data['assignee_ids']);
        }

        DB::transaction(function () use ($assignedTask, $data, $previousStatus): void {
            $payload = collect($data)->except(['assignee_ids'])->all();

            if (array_key_exists('status', $payload)) {
                $newStatus = $payload['status'];
                if ($newStatus === AssignedTaskStatus::InProgress->value && $assignedTask->started_at === null) {
                    $payload['started_at'] = now();
                }
                if ($newStatus === AssignedTaskStatus::Done->value) {
                    $payload['completed_at'] = now();
                    $payload['progress_percentage'] = 100;
                }
                if ($previousStatus === AssignedTaskStatus::Done->value && $newStatus !== AssignedTaskStatus::Done->value) {
                    $payload['completed_at'] = null;
                }
            }

            $assignedTask->fill($payload);
            $assignedTask->save();

            if (array_key_exists('assignee_ids', $data)) {
                $assignedTask->assignees()->sync(
                    StaffUserResolver::filterStaffIds(
                        array_values(array_unique(array_map('intval', $data['assignee_ids'])))
                    )
                );
            }
        });

        $assignedTask->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email']);

        $newStatus = $assignedTask->status?->value ?? (string) $assignedTask->status;
        if ($previousStatus !== AssignedTaskStatus::Done->value && $newStatus === AssignedTaskStatus::Done->value) {
            $this->opsNotifications->taskCompleted($assignedTask, $user);
        }

        if (array_key_exists('assignee_ids', $data)) {
            $this->opsNotifications->taskAssigned($assignedTask);
        }

        return BaseResponse::ok([
            'message' => __('Tâche mise à jour.'),
            'assigned_task' => new AssignedTaskResource($assignedTask),
        ])->toJsonResponse();
    }

    public function renew(Request $request, AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('create', AssignedTask::class);
        $user = $request->user();
        abort_if($user === null, 401);

        $data = $request->validate([
            'due_date' => ['nullable', 'date'],
            'week_start_date' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'assignee_ids' => ['sometimes', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $assignedTask->load('assignees');

        $copy = DB::transaction(function () use ($assignedTask, $data, $user): AssignedTask {
            $weekStart = $data['week_start_date']
                ?? Carbon::now('Africa/Douala')->startOfWeek(Carbon::MONDAY)->toDateString();

            $copy = AssignedTask::query()->create([
                'title' => $assignedTask->title,
                'description' => $assignedTask->description,
                'meeting_id' => $assignedTask->meeting_id,
                'due_date' => $data['due_date'] ?? Carbon::parse($weekStart)->addDays(4)->toDateString(),
                'estimated_minutes' => $data['estimated_minutes'] ?? $assignedTask->estimated_minutes,
                'week_start_date' => $weekStart,
                'priority' => $assignedTask->priority?->value ?? AssignedTaskPriority::Medium->value,
                'progress_percentage' => 0,
                'status' => AssignedTaskStatus::Todo->value,
                'notes' => $assignedTask->notes,
                'created_by' => $user->id,
                'renewed_from_id' => $assignedTask->id,
            ]);

            $assigneeIds = array_key_exists('assignee_ids', $data)
                ? StaffUserResolver::filterStaffIds(array_map('intval', $data['assignee_ids']))
                : $assignedTask->assignees->pluck('id')->map(fn ($id) => (int) $id)->all();

            $copy->assignees()->sync($assigneeIds);

            return $copy;
        });

        $copy->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email', 'renewedFrom:id,title']);
        $this->opsNotifications->taskAssigned($copy);

        return BaseResponse::created([
            'message' => __('Tâche reconduite.'),
            'assigned_task' => new AssignedTaskResource($copy),
        ])->toJsonResponse();
    }

    public function destroy(AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('delete', $assignedTask);
        $assignedTask->delete();

        return BaseResponse::ok(['message' => __('Tâche supprimée.')])->toJsonResponse();
    }
}
