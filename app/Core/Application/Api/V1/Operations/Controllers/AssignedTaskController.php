<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\AssignedTaskResource;
use App\Core\Domain\Operations\Enums\AssignedTaskPriority;
use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class AssignedTaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AssignedTask::class);

        $items = AssignedTask::query()
            ->with(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('priority'), fn ($q, $p) => $q->where('priority', $p))
            ->when($request->query('meeting_id'), fn ($q, $id) => $q->where('meeting_id', (int) $id))
            ->when($request->query('assignee_id'), function ($q) use ($request): void {
                $assigneeId = (int) $request->query('assignee_id');
                $q->whereHas('assignees', fn ($inner) => $inner->where('users.id', $assigneeId));
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
            'priority' => ['sometimes', Rule::in(AssignedTaskPriority::values())],
            'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', Rule::in(AssignedTaskStatus::values())],
            'assignee_ids' => ['sometimes', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $task = DB::transaction(function () use ($data, $user): AssignedTask {
            $task = AssignedTask::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'meeting_id' => $data['meeting_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'priority' => $data['priority'] ?? AssignedTaskPriority::Medium->value,
                'progress_percentage' => $data['progress_percentage'] ?? 0,
                'status' => $data['status'] ?? AssignedTaskStatus::Todo->value,
                'created_by' => $user->id,
            ]);

            $assigneeIds = array_values(array_unique(array_map('intval', $data['assignee_ids'] ?? [])));
            if ($assigneeIds !== []) {
                $task->assignees()->sync($assigneeIds);
            }

            return $task;
        });

        $task->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email']);

        return BaseResponse::created([
            'message' => __('Tâche assignée créée.'),
            'assigned_task' => new AssignedTaskResource($task),
        ])->toJsonResponse();
    }

    public function show(AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('view', $assignedTask);
        $assignedTask->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email', 'dailyTasks']);

        return BaseResponse::ok([
            'assigned_task' => new AssignedTaskResource($assignedTask),
        ])->toJsonResponse();
    }

    public function update(Request $request, AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('update', $assignedTask);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meeting_id' => ['nullable', 'integer', 'exists:meetings,id'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['sometimes', Rule::in(AssignedTaskPriority::values())],
            'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', Rule::in(AssignedTaskStatus::values())],
            'final_result' => ['nullable', 'string'],
            'assignee_ids' => ['sometimes', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($assignedTask, $data): void {
            $assignedTask->fill(collect($data)->except(['assignee_ids'])->all());
            $assignedTask->save();

            if (array_key_exists('assignee_ids', $data)) {
                $assignedTask->assignees()->sync(
                    array_values(array_unique(array_map('intval', $data['assignee_ids'])))
                );
            }
        });

        $assignedTask->load(['creator:id,name', 'meeting:id,title', 'assignees:id,name,email']);

        return BaseResponse::ok([
            'message' => __('Tâche mise à jour.'),
            'assigned_task' => new AssignedTaskResource($assignedTask),
        ])->toJsonResponse();
    }

    public function destroy(AssignedTask $assignedTask): JsonResponse
    {
        $this->authorize('delete', $assignedTask);
        $assignedTask->delete();

        return BaseResponse::ok(['message' => __('Tâche supprimée.')])->toJsonResponse();
    }
}
