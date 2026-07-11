<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Partner\Models\PartnerCohort;

final class UpdatePartnerCohortAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(PartnerCohort $cohort, array $data): PartnerCohort
    {
        if (! $cohort->isEditableByPartner()) {
            abort(422, __('Cette cohorte ne peut plus être modifiée.'));
        }

        $cohort->fill(array_intersect_key($data, array_flip([
            'name',
            'academic_year',
            'field_of_study',
            'start_date',
            'end_date',
            'expected_student_count',
            'description',
        ])));
        $cohort->save();

        return $cohort->fresh(['requiredDocuments', 'organization']);
    }
}
