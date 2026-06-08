<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Resources;

use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecruiterOnboardingApplication */
final class RecruiterOnboardingApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'legal_form' => $this->legal_form,
            'registration_number' => $this->registration_number,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address' => $this->address,
            'website' => $this->website,
            'activity_description' => $this->activity_description,
            'desired_slug' => $this->desired_slug,
            'status' => $this->status?->value ?? $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'staff_note' => $this->staff_note,
            'rejection_reason' => $this->rejection_reason,
            'organization' => new RecruiterOrganizationResource($this->whenLoaded('organization')),
            'applicant' => $this->whenLoaded('applicant', fn () => [
                'id' => $this->applicant?->id,
                'name' => $this->applicant?->name,
                'email' => $this->applicant?->email,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
            ]),
            'documents' => $this->whenLoaded('documents', fn () => $this->documents->map(fn ($doc) => [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'original_filename' => $doc->original_filename,
                'mime_type' => $doc->mime_type,
                'size_bytes' => $doc->size_bytes,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
