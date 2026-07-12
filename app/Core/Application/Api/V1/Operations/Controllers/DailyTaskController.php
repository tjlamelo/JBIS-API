<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\DailyTaskResource;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Enums\DailyTaskStatus;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\DailyTask;
use App\Core\Domain\Operations\Support\AssignedTaskTimeTracker;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class DailyTaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DailyTask::class);
        $user = $request->user();
        abort_if($user === null, 401);

        $isStaff = $user->hasAnyRole(ApplicationRole::STAFF_ROLES);

        $items = DailyTask::query()
            ->with(['user:id,name,email', 'assignedTask:id,title'])
            ->when(! $isStaff, fn ($q) => $q->where('user_id', $user->id))
            ->when($isStaff && $request->query('user_id'), fn ($q, $id) => $q->where('user_id', (int) $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('assigned_task_id'), fn ($q, $id) => $q->where('assigned_task_id', (int) $id))
            ->when($request->query('outside_meeting') === '1', fn ($q) => $q->where('is_outside_meeting', true))
            ->when($request->query('task_date'), fn ($q, $d) => $q->whereDate('task_date', $d))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('task_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('task_date', '<=', $d))
            ->when($request->query('q'), function ($q) use ($request): void {
                $term = '%'.trim((string) $request->query('q')).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('task_date')
            ->orderByDesc('id')
            ->paginate(max(1, min(100, (int) $request->integer('per_page', 20))));

        return BaseResponse::ok([
            'daily_tasks' => DailyTaskResource::collection($items->items()),
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
        $this->authorize('create', DailyTask::class);
        $user = $request->user();
        abort_if($user === null, 401);

        $isStaff = $user->hasAnyRole(ApplicationRole::STAFF_ROLES);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => [$isStaff ? 'sometimes' : 'prohibited', 'integer', 'exists:users,id'],
            'assigned_task_id' => ['nullable', 'integer', 'exists:assigned_tasks,id'],
            'is_outside_meeting' => ['sometimes', 'boolean'],
            'task_date' => ['required', 'date'],
            'hours_spent' => ['nullable', 'integer', 'min:0', 'max:24'],
            'minutes_spent' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'status' => ['required', Rule::in(DailyTaskStatus::values())],
            'blockers_notes' => ['nullable', 'string'],
        ]);

        $minutes = $data['minutes_spent'] ?? null;
        if ($minutes === null && isset($data['hours_spent'])) {
            $minutes = ((int) $data['hours_spent']) * 60;
        }

        $daily = DailyTask::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'user_id' => $isStaff && isset($data['user_id']) ? (int) $data['user_id'] : $user->id,
            'assigned_task_id' => $data['assigned_task_id'] ?? null,
            'is_outside_meeting' => array_key_exists('is_outside_meeting', $data)
                ? (bool) $data['is_outside_meeting']
                : empty($data['assigned_task_id']),
            'task_date' => $data['task_date'],
            'hours_spent' => $data['hours_spent'] ?? (int) floor(((int) ($minutes ?? 0)) / 60),
            'minutes_spent' => $minutes ?? 0,
            'status' => $data['status'],
            'blockers_notes' => $data['blockers_notes'] ?? null,
        ]);

        if ($daily->assigned_task_id) {
            $task = AssignedTask::query()->find($daily->assigned_task_id);
            if ($task) {
                AssignedTaskTimeTracker::refreshMinutesSpent($task);
            }
        }

        $daily->load(['user:id,name,email', 'assignedTask:id,title']);

        return BaseResponse::created([
            'message' => __('Tâche journalière enregistrée.'),
            'daily_task' => new DailyTaskResource($daily),
        ])->toJsonResponse();
    }

    public function show(DailyTask $dailyTask): JsonResponse
    {
        $this->authorize('view', $dailyTask);
        $dailyTask->load(['user:id,name,email', 'assignedTask:id,title']);

        return BaseResponse::ok([
            'daily_task' => new DailyTaskResource($dailyTask),
        ])->toJsonResponse();
    }

    public function update(Request $request, DailyTask $dailyTask): JsonResponse
    {
        $this->authorize('update', $dailyTask);
        $user = $request->user();
        abort_if($user === null, 401);
        $isStaff = $user->hasAnyRole(ApplicationRole::STAFF_ROLES);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => [$isStaff ? 'sometimes' : 'prohibited', 'integer', 'exists:users,id'],
            'assigned_task_id' => ['nullable', 'integer', 'exists:assigned_tasks,id'],
            'is_outside_meeting' => ['sometimes', 'boolean'],
            'task_date' => ['sometimes', 'date'],
            'hours_spent' => ['nullable', 'integer', 'min:0', 'max:24'],
            'minutes_spent' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'status' => ['sometimes', Rule::in(DailyTaskStatus::values())],
            'blockers_notes' => ['nullable', 'string'],
        ]);

        if (array_key_exists('minutes_spent', $data) && ! array_key_exists('hours_spent', $data)) {
            $data['hours_spent'] = (int) floor(((int) $data['minutes_spent']) / 60);
        }

        $previousAssignedId = $dailyTask->assigned_task_id;
        $dailyTask->fill($data);
        $dailyTask->save();

        $ids = array_filter([$previousAssignedId, $dailyTask->assigned_task_id]);
        foreach ($ids as $id) {
            $task = AssignedTask::query()->find($id);
            if ($task) {
                AssignedTaskTimeTracker::refreshMinutesSpent($task);
            }
        }

        $dailyTask->load(['user:id,name,email', 'assignedTask:id,title']);

        return BaseResponse::ok([
            'message' => __('Tâche journalière mise à jour.'),
            'daily_task' => new DailyTaskResource($dailyTask),
        ])->toJsonResponse();
    }

    public function destroy(DailyTask $dailyTask): JsonResponse
    {
        $this->authorize('delete', $dailyTask);
        $assignedId = $dailyTask->assigned_task_id;
        $dailyTask->delete();

        if ($assignedId) {
            $task = AssignedTask::query()->find($assignedId);
            if ($task) {
                AssignedTaskTimeTracker::refreshMinutesSpent($task);
            }
        }

        return BaseResponse::ok(['message' => __('Tâche journalière supprimée.')])->toJsonResponse();
    }
}
