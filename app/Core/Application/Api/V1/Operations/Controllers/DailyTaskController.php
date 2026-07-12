<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\DailyTaskResource;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Enums\DailyTaskStatus;
use App\Core\Domain\Operations\Models\DailyTask;
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
            'task_date' => ['required', 'date'],
            'hours_spent' => ['nullable', 'integer', 'min:0', 'max:24'],
            'status' => ['required', Rule::in(DailyTaskStatus::values())],
            'blockers_notes' => ['nullable', 'string'],
        ]);

        $daily = DailyTask::query()->create([
            ...$data,
            'user_id' => $isStaff && isset($data['user_id']) ? (int) $data['user_id'] : $user->id,
            'hours_spent' => $data['hours_spent'] ?? 0,
        ]);

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
            'task_date' => ['sometimes', 'date'],
            'hours_spent' => ['nullable', 'integer', 'min:0', 'max:24'],
            'status' => ['sometimes', Rule::in(DailyTaskStatus::values())],
            'blockers_notes' => ['nullable', 'string'],
        ]);

        $dailyTask->fill($data);
        $dailyTask->save();
        $dailyTask->load(['user:id,name,email', 'assignedTask:id,title']);

        return BaseResponse::ok([
            'message' => __('Tâche journalière mise à jour.'),
            'daily_task' => new DailyTaskResource($dailyTask),
        ])->toJsonResponse();
    }

    public function destroy(DailyTask $dailyTask): JsonResponse
    {
        $this->authorize('delete', $dailyTask);
        $dailyTask->delete();

        return BaseResponse::ok(['message' => __('Tâche journalière supprimée.')])->toJsonResponse();
    }
}
