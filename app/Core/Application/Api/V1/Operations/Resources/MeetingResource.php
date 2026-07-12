<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Resources;

use App\Core\Domain\Operations\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Meeting */
final class MeetingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value ?? $this->type,
            'title' => $this->title,
            'location' => $this->location,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'agenda' => $this->agenda,
            'minutes' => $this->minutes,
            'decisions' => $this->decisions,
            'organizer_id' => $this->organizer_id,
            'status' => $this->status?->value ?? $this->status,
            'organizer' => $this->whenLoaded('organizer', fn () => $this->organizer ? [
                'id' => $this->organizer->id,
                'name' => $this->organizer->name,
                'email' => $this->organizer->email,
            ] : null),
            'attendees' => $this->whenLoaded('attendees', fn () => $this->attendees->map(static fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_present' => (bool) $u->pivot?->is_present,
                'excuse_reason' => $u->pivot?->excuse_reason,
            ])->values()),
            'assigned_tasks_count' => $this->whenCounted('assignedTasks'),
            'assigned_tasks' => $this->whenLoaded('assignedTasks', fn () => AssignedTaskResource::collection($this->assignedTasks)),
            'can_add_own_tasks' => $this->when(
                $request->user() !== null,
                function () use ($request) {
                    $user = $request->user();
                    if ($user === null) {
                        return false;
                    }
                    if ((int) $this->organizer_id === (int) $user->id) {
                        return true;
                    }
                    $attendee = $this->relationLoaded('attendees')
                        ? $this->attendees->firstWhere('id', $user->id)
                        : null;

                    return $attendee !== null && (bool) $attendee->pivot?->is_present;
                }
            ),
            'can_assign_to_others' => $this->when(
                $request->user() !== null,
                function () use ($request) {
                    $user = $request->user();
                    if ($user === null) {
                        return false;
                    }

                    if ($user->hasAnyRole([
                        \App\Core\Domain\Identity\Support\ApplicationRole::SUPERADMIN,
                        \App\Core\Domain\Identity\Support\ApplicationRole::ADMIN,
                    ])) {
                        return true;
                    }

                    return (int) $this->organizer_id === (int) $user->id;
                }
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
