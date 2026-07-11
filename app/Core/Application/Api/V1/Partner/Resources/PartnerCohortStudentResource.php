<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Resources;

use App\Core\Domain\Partner\Models\PartnerCohortStudent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnerCohortStudent */
final class PartnerCohortStudentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mandatoryTotal = $this->relationLoaded('documents')
            ? $this->documents->where('is_mandatory', true)->count()
            : null;

        return [
            'id' => $this->id,
            'invited_name' => $this->invited_name,
            'invited_email' => $this->invited_email,
            'enrollment_status' => $this->enrollment_status?->value ?? $this->enrollment_status,
            'placement_status' => $this->placement_status?->value ?? $this->placement_status,
            'partner_notes' => $this->partner_notes,
            'staff_notes' => $this->staff_notes,
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'student_user_id' => $this->student_user_id,
            'user_internship_id' => $this->user_internship_id,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student?->id,
                'name' => $this->student?->name,
                'email' => $this->student?->email,
            ]),
            'documents' => PartnerCohortStudentDocumentResource::collection($this->whenLoaded('documents')),
            'documents_complete' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->documents->where('status', '!=', 'missing')->count()
            ),
            'documents_missing_mandatory' => $this->when(
                $this->relationLoaded('documents') && $this->relationLoaded('cohort'),
                function () {
                    $mandatoryCodes = $this->cohort?->requiredDocuments
                        ?->where('is_mandatory', true)
                        ->pluck('document_type_code')
                        ->all() ?? [];

                    return $this->documents
                        ->whereIn('document_type_code', $mandatoryCodes)
                        ->where('status', 'missing')
                        ->count();
                }
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
