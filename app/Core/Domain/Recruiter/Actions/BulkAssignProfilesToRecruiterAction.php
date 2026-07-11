<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Illuminate\Support\Facades\DB;

final class BulkAssignProfilesToRecruiterAction
{
    public function __construct(
        private readonly AssignProfileToRecruiterAction $assignProfile,
    ) {}

    /**
     * @param  list<int>  $candidateUserIds
     * @param  list<string>|null  $visibleSections
     * @param  list<string>|null  $maskedFields
     * @return array{
     *     assigned_count: int,
     *     skipped_count: int,
     *     skipped_not_approved: int,
     *     candidate_user_ids: list<int>
     * }
     */
    public function execute(
        RecruiterOrganization $organization,
        array $candidateUserIds,
        User $assignedBy,
        ?string $note = null,
        ?array $visibleSections = null,
        ?array $maskedFields = null,
        ?int $profileRequestId = null,
    ): array {
        $candidateUserIds = array_values(array_unique(array_map('intval', $candidateUserIds)));
        $assignedIds = [];
        $skipped = 0;
        $skippedNotApproved = 0;

        DB::transaction(function () use (
            $organization,
            $candidateUserIds,
            $assignedBy,
            $note,
            $visibleSections,
            $maskedFields,
            $profileRequestId,
            &$assignedIds,
            &$skipped,
            &$skippedNotApproved,
        ): void {
            foreach ($candidateUserIds as $candidateUserId) {
                if ($candidateUserId <= 0) {
                    $skipped++;
                    continue;
                }

                /** @var User|null $candidate */
                $candidate = User::query()->with('profile')->find($candidateUserId);
                if ($candidate === null) {
                    $skipped++;
                    continue;
                }

                if ($candidate->profile === null || ! $candidate->profile->is_approved) {
                    $skipped++;
                    $skippedNotApproved++;
                    continue;
                }

                try {
                    $this->assignProfile->execute(
                        $organization,
                        $candidate,
                        $assignedBy,
                        $note,
                        $visibleSections,
                        $maskedFields,
                        $profileRequestId,
                    );
                    $assignedIds[] = $candidateUserId;
                } catch (\InvalidArgumentException) {
                    $skipped++;
                    $skippedNotApproved++;
                }
            }
        });

        return [
            'assigned_count' => count($assignedIds),
            'skipped_count' => $skipped,
            'skipped_not_approved' => $skippedNotApproved,
            'candidate_user_ids' => $assignedIds,
        ];
    }
}
