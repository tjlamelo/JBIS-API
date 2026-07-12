<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Operations\Resources\MeetingResource;
use App\Core\Domain\Operations\Enums\MeetingStatus;
use App\Core\Domain\Operations\Enums\MeetingType;
use App\Core\Domain\Operations\Models\Meeting;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class MeetingController extends Controller
{
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
            'status' => ['sometimes', Rule::in(MeetingStatus::values())],
            'attendee_ids' => ['sometimes', 'array'],
            'attendee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $meeting = DB::transaction(function () use ($data, $user): Meeting {
            $meeting = Meeting::query()->create([
                'title' => $data['title'],
                'type' => $data['type'],
                'location' => $data['location'] ?? null,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'agenda' => $data['agenda'] ?? null,
                'organizer_id' => $user->id,
                'status' => $data['status'] ?? MeetingStatus::Scheduled->value,
            ]);

            $attendeeIds = array_values(array_unique(array_map('intval', $data['attendee_ids'] ?? [])));
            if ($attendeeIds !== []) {
                $meeting->attendees()->sync(
                    collect($attendeeIds)->mapWithKeys(static fn (int $id) => [$id => ['is_present' => false]])->all()
                );
            }

            return $meeting;
        });

        $meeting->load(['organizer:id,name,email', 'attendees:id,name,email']);

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
            'attendee_ids' => ['sometimes', 'array'],
            'attendee_ids.*' => ['integer', 'exists:users,id'],
            'attendance' => ['sometimes', 'array'],
            'attendance.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance.*.is_present' => ['required', 'boolean'],
            'attendance.*.excuse_reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($meeting, $data): void {
            $meeting->fill(collect($data)->except(['attendee_ids', 'attendance'])->all());
            $meeting->save();

            if (array_key_exists('attendee_ids', $data)) {
                $attendeeIds = array_values(array_unique(array_map('intval', $data['attendee_ids'])));
                $sync = [];
                foreach ($attendeeIds as $id) {
                    $sync[$id] = ['is_present' => false];
                }
                $meeting->attendees()->sync($sync);
            }

            if (! empty($data['attendance'])) {
                foreach ($data['attendance'] as $row) {
                    $meeting->attendees()->updateExistingPivot((int) $row['user_id'], [
                        'is_present' => (bool) $row['is_present'],
                        'excuse_reason' => $row['excuse_reason'] ?? null,
                    ]);
                }
            }
        });

        $meeting->load(['organizer:id,name,email', 'attendees:id,name,email']);

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
}
