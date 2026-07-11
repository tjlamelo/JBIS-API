<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use Illuminate\Support\Facades\DB;

final class TransmitRecruiterProfileRequestAction
{
    public function __construct(
        private readonly BulkAssignProfilesToRecruiterAction $bulkAssign,
    ) {}

    /**
     * @param  list<int>|null  $candidateUserIds
     * @param  list<string>|null  $visibleSections
     * @param  list<string>|null  $maskedFields
     * @return array{request: RecruiterProfileRequest, bulk: array<string, mixed>}
     */
    public function execute(
        RecruiterProfileRequest $request,
        User $staff,
        ?array $candidateUserIds = null,
        ?string $note = null,
        ?array $visibleSections = null,
        ?array $maskedFields = null,
    ): array {
        if (! $request->canBeTransmitted()) {
            throw new \InvalidArgumentException(__('Aucun candidat à transmettre pour cette demande.'));
        }

        $matched = array_map('intval', $request->matched_candidate_ids ?? []);
        $candidateUserIds = $candidateUserIds !== null
            ? array_values(array_intersect(array_map('intval', $candidateUserIds), $matched))
            : $matched;

        if ($candidateUserIds === []) {
            throw new \InvalidArgumentException(__('Sélectionnez au moins un candidat correspondant.'));
        }

        return DB::transaction(function () use ($request, $staff, $candidateUserIds, $note, $visibleSections, $maskedFields): array {
            $organization = $request->organization;
            if ($organization === null) {
                throw new \InvalidArgumentException(__('Organisation introuvable.'));
            }

            $bulk = $this->bulkAssign->execute(
                $organization,
                $candidateUserIds,
                $staff,
                $note ?? $request->note,
                $visibleSections,
                $maskedFields,
                $request->id,
            );

            $request->status = RecruiterProfileRequestStatus::Transmitted;
            $request->transmitted_at = now();
            $request->transmitted_by_user_id = $staff->id;
            $request->transmitted_candidate_ids = $bulk['candidate_user_ids'];
            $request->save();

            return [
                'request' => $request->fresh(['organization', 'submittedBy:id,name,email', 'transmittedBy:id,name']),
                'bulk' => $bulk,
            ];
        });
    }
}
