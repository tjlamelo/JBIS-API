<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Resources;

use App\Core\Domain\Partner\Models\PartnerCohort;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnerCohort */
final class PartnerCohortResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'name' => $this->name,
            'academic_year' => $this->academic_year,
            'field_of_study' => $this->field_of_study,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'expected_student_count' => $this->expected_student_count,
            'description' => $this->description,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'staff_note' => $this->staff_note,
            'rejection_reason' => $this->rejection_reason,
            'students_count' => $this->whenCounted('students'),
            'documents_complete_count' => $this->when(isset($this->documents_complete_count), fn () => $this->documents_complete_count),
            'organization' => new PartnerOrganizationResource($this->whenLoaded('organization')),
            'required_documents' => PartnerCohortRequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
            'students' => PartnerCohortStudentResource::collection($this->whenLoaded('students')),
            'submitted_by' => $this->whenLoaded('submittedBy', fn () => [
                'id' => $this->submittedBy?->id,
                'name' => $this->submittedBy?->name,
                'email' => $this->submittedBy?->email,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
