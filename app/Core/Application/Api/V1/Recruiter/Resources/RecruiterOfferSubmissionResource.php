<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Resources;

use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecruiterOfferSubmission */
final class RecruiterOfferSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'payload' => $this->payload,
            'offer_id' => $this->offer_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'staff_note' => $this->staff_note,
            'rejection_reason' => $this->rejection_reason,
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
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
