<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Operations\Resources;

use App\Core\Domain\Operations\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DailyTask */
final class DailyTaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'assigned_task_id' => $this->assigned_task_id,
            'title' => $this->title,
            'description' => $this->description,
            'task_date' => $this->task_date?->toDateString(),
            'hours_spent' => $this->hours_spent,
            'status' => $this->status?->value ?? $this->status,
            'blockers_notes' => $this->blockers_notes,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'assigned_task' => $this->whenLoaded('assignedTask', fn () => $this->assignedTask ? [
                'id' => $this->assignedTask->id,
                'title' => $this->assignedTask->title,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
