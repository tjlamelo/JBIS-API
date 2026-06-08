<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Resources;

use App\Core\Application\Api\V1\Identity\Resources\AdminUserResource;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecruiterProfileAssignment */
final class RecruiterAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'note' => $this->note,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'revoked_at' => $this->revoked_at?->toIso8601String(),
            'organization' => new RecruiterOrganizationResource($this->whenLoaded('organization')),
            'candidate' => new AdminUserResource($this->whenLoaded('candidate')),
            'assigned_by' => $this->whenLoaded('assignedBy', fn () => [
                'id' => $this->assignedBy?->id,
                'name' => $this->assignedBy?->name,
            ]),
        ];
    }
}
