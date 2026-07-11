<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Resources;

use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecruiterProfileRequest */
final class RecruiterProfileRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'title' => $this->title,
            'criteria' => $this->criteria,
            'quantity_needed' => $this->quantity_needed,
            'note' => $this->note,
            'matched_candidate_ids' => $this->matched_candidate_ids,
            'matched_count' => $this->matched_count,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'staff_note' => $this->staff_note,
            'rejection_reason' => $this->rejection_reason,
            'transmitted_at' => $this->transmitted_at?->toIso8601String(),
            'transmitted_candidate_ids' => $this->transmitted_candidate_ids,
            'organization' => new RecruiterOrganizationResource($this->whenLoaded('organization')),
            'submitted_by' => $this->whenLoaded('submittedBy', fn () => [
                'id' => $this->submittedBy?->id,
                'name' => $this->submittedBy?->name,
                'email' => $this->submittedBy?->email,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
            ]),
            'transmitted_by' => $this->whenLoaded('transmittedBy', fn () => [
                'id' => $this->transmittedBy?->id,
                'name' => $this->transmittedBy?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
