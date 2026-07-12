<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Resources;

use App\Core\Domain\Operations\Models\AssignedTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AssignedTask */
final class AssignedTaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $estimated = (int) ($this->estimated_minutes ?? 0);
        $spent = (int) ($this->minutes_spent ?? 0);

        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toDateString(),
            'estimated_minutes' => $this->estimated_minutes,
            'minutes_spent' => $this->minutes_spent,
            'time_remaining_minutes' => $estimated > 0 ? max(0, $estimated - $spent) : null,
            'time_overrun_minutes' => $estimated > 0 ? max(0, $spent - $estimated) : null,
            'week_start_date' => $this->week_start_date?->toDateString(),
            'priority' => $this->priority?->value ?? $this->priority,
            'progress_percentage' => $this->progress_percentage,
            'status' => $this->status?->value ?? $this->status,
            'final_result' => $this->final_result,
            'notes' => $this->notes,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'renewed_from_id' => $this->renewed_from_id,
            'is_overdue' => $this->resource->isOverdue(),
            'meeting' => $this->whenLoaded('meeting', fn () => $this->meeting ? [
                'id' => $this->meeting->id,
                'title' => $this->meeting->title,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'renewed_from' => $this->whenLoaded('renewedFrom', fn () => $this->renewedFrom ? [
                'id' => $this->renewedFrom->id,
                'title' => $this->renewedFrom->title,
            ] : null),
            'assignees' => $this->whenLoaded('assignees', fn () => $this->assignees->map(static fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values()),
            'daily_tasks' => $this->whenLoaded('dailyTasks', fn () => DailyTaskResource::collection($this->dailyTasks)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
