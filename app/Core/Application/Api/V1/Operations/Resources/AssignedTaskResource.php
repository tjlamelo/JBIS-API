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
        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toDateString(),
            'priority' => $this->priority?->value ?? $this->priority,
            'progress_percentage' => $this->progress_percentage,
            'status' => $this->status?->value ?? $this->status,
            'final_result' => $this->final_result,
            'meeting' => $this->whenLoaded('meeting', fn () => $this->meeting ? [
                'id' => $this->meeting->id,
                'title' => $this->meeting->title,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'assignees' => $this->whenLoaded('assignees', fn () => $this->assignees->map(static fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
