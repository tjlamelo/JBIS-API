<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Partner\Enums\PartnerCohortStudentDocumentStatus;
use App\Core\Domain\Partner\Models\PartnerOrganization;

final class GetPartnerDashboardStatsAction
{
    /**
     * @return array{
     *   students_total: int,
     *   documents_complete: int,
     *   placements_validated: int,
     *   cohorts_active: int,
     * }
     */
    public function execute(PartnerOrganization $organization): array
    {
        $studentsQuery = $organization->cohorts()
            ->withCount([
                'students',
                'students as documents_complete_count' => function ($q): void {
                    $q->whereDoesntHave('documents', function ($docQ): void {
                        $docQ->where('status', PartnerCohortStudentDocumentStatus::Missing->value);
                    })->whereHas('documents');
                },
                'students as placements_validated_count' => function ($q): void {
                    $q->whereIn('placement_status', ['placed', 'completed']);
                },
            ]);

        $cohorts = $studentsQuery->get();

        return [
            'students_total' => (int) $cohorts->sum('students_count'),
            'documents_complete' => (int) $cohorts->sum('documents_complete_count'),
            'placements_validated' => (int) $cohorts->sum('placements_validated_count'),
            'cohorts_active' => $organization->cohorts()->where('status', 'active')->count(),
        ];
    }
}
