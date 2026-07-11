<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Enums\PartnerCohortStatus;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Core\Domain\Partner\Models\PartnerOrganization;
use App\Core\Domain\Partner\Support\PartnerCohortDocumentDefaults;
use Illuminate\Support\Facades\DB;

final class CreatePartnerCohortAction
{
    /**
     * @param  array{
     *   name: string,
     *   academic_year?: string|null,
     *   field_of_study?: string|null,
     *   start_date?: string|null,
     *   end_date?: string|null,
     *   expected_student_count?: int|null,
     *   description?: string|null,
     * }  $data
     */
    public function execute(PartnerOrganization $organization, User $creator, array $data): PartnerCohort
    {
        return DB::transaction(function () use ($organization, $creator, $data): PartnerCohort {
            $cohort = PartnerCohort::query()->create([
                'partner_organization_id' => $organization->id,
                'name' => $data['name'],
                'academic_year' => $data['academic_year'] ?? null,
                'field_of_study' => $data['field_of_study'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'expected_student_count' => (int) ($data['expected_student_count'] ?? 0),
                'description' => $data['description'] ?? null,
                'status' => PartnerCohortStatus::Draft,
                'settings' => [],
            ]);

            foreach (PartnerCohortDocumentDefaults::requiredDocuments() as $doc) {
                $cohort->requiredDocuments()->create($doc);
            }

            return $cohort->load(['requiredDocuments', 'organization']);
        });
    }
}
