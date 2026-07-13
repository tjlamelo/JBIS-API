<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Partner\Enums\PartnerCohortStudentDocumentStatus;
use App\Core\Domain\Partner\Models\PartnerCohortStudent;
use App\Core\Domain\Partner\Models\PartnerOrganization;

final class GetPartnerDashboardStatsAction
{
    /**
     * @return array{
     *   students_total: int,
     *   documents_complete: int,
     *   placements_validated: int,
     *   cohorts_active: int,
     *   charts: array{
     *     cohorts_by_status: list<array{key: string, label: string, value: int}>,
     *     students_by_placement: list<array{key: string, label: string, value: int}>,
     *     students_per_cohort: list<array{key: string, label: string, value: int}>
     *   }
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
        $orgId = (int) $organization->id;

        $placementCounts = PartnerCohortStudent::query()
            ->whereHas('cohort', fn ($q) => $q->where('partner_organization_id', $orgId))
            ->selectRaw('placement_status, COUNT(*) as aggregate')
            ->groupBy('placement_status')
            ->pluck('aggregate', 'placement_status');

        $placementLabels = [
            'pending' => 'En attente',
            'matched' => 'Matchés',
            'placed' => 'Placés',
            'completed' => 'Terminés',
        ];

        return [
            'students_total' => (int) $cohorts->sum('students_count'),
            'documents_complete' => (int) $cohorts->sum('documents_complete_count'),
            'placements_validated' => (int) $cohorts->sum('placements_validated_count'),
            'cohorts_active' => $organization->cohorts()->where('status', 'active')->count(),
            'charts' => [
                'cohorts_by_status' => $organization->cohorts()
                    ->selectRaw('status, COUNT(*) as aggregate')
                    ->groupBy('status')
                    ->get()
                    ->map(static fn ($row): array => [
                        'key' => (string) $row->status,
                        'label' => match ((string) $row->status) {
                            'draft' => 'Brouillon',
                            'submitted' => 'Soumise',
                            'under_review' => 'En revue',
                            'active' => 'Active',
                            'closed' => 'Clôturée',
                            'rejected' => 'Rejetée',
                            default => (string) $row->status,
                        },
                        'value' => (int) $row->aggregate,
                    ])
                    ->values()
                    ->all(),
                'students_by_placement' => collect($placementLabels)
                    ->map(static fn (string $label, string $key): array => [
                        'key' => $key,
                        'label' => $label,
                        'value' => (int) ($placementCounts[$key] ?? 0),
                    ])
                    ->values()
                    ->all(),
                'students_per_cohort' => $cohorts
                    ->sortByDesc('students_count')
                    ->take(8)
                    ->map(static fn ($cohort): array => [
                        'key' => (string) $cohort->id,
                        'label' => (string) $cohort->name,
                        'value' => (int) $cohort->students_count,
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }
}
