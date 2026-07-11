<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentDocumentStatus;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentEnrollmentStatus;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Core\Domain\Partner\Models\PartnerCohortStudent;
use Illuminate\Support\Facades\DB;

final class AddPartnerCohortStudentAction
{
    /**
     * @param  array{
     *   invited_name: string,
     *   invited_email?: string|null,
     *   student_user_id?: int|null,
     *   partner_notes?: string|null,
     * }  $data
     */
    public function execute(PartnerCohort $cohort, User $enrolledBy, array $data): PartnerCohortStudent
    {
        if (! $cohort->isEditableByPartner()) {
            abort(422, __('Impossible d\'ajouter un étudiant à cette cohorte.'));
        }

        return DB::transaction(function () use ($cohort, $enrolledBy, $data): PartnerCohortStudent {
            $student = PartnerCohortStudent::query()->create([
                'partner_cohort_id' => $cohort->id,
                'invited_name' => $data['invited_name'],
                'invited_email' => $data['invited_email'] ?? null,
                'student_user_id' => $data['student_user_id'] ?? null,
                'enrollment_status' => isset($data['student_user_id'])
                    ? PartnerCohortStudentEnrollmentStatus::Registered
                    : PartnerCohortStudentEnrollmentStatus::Invited,
                'partner_notes' => $data['partner_notes'] ?? null,
                'enrolled_at' => now(),
                'enrolled_by_user_id' => $enrolledBy->id,
            ]);

            foreach ($cohort->requiredDocuments as $required) {
                $student->documents()->create([
                    'document_type_code' => $required->document_type_code,
                    'status' => PartnerCohortStudentDocumentStatus::Missing,
                ]);
            }

            if ($student->student_user_id) {
                app(SyncPartnerCohortStudentChecklistAction::class)->execute($student);
            }

            return $student->load(['documents', 'student:id,name,email']);
        });
    }
}
