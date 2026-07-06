<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\UserPersonName;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterDashboardStatsQuery
{
    private const PENDING_OFFER_STATUSES = [
        RecruiterOfferSubmissionStatus::Submitted,
        RecruiterOfferSubmissionStatus::InReview,
        RecruiterOfferSubmissionStatus::NeedsChanges,
    ];

    public function __construct(
        private readonly RecruiterAccess $recruiterAccess,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $organization = $this->recruiterAccess->primaryOrganization($user);

        if ($organization === null) {
            return [
                'organization' => null,
                'stats' => [
                    'assignments_active' => 0,
                    'offers_pending' => 0,
                    'offers_total' => 0,
                ],
                'recent_assignments' => [],
            ];
        }

        $orgId = (int) $organization->id;
        $pendingOfferValues = array_map(
            static fn (RecruiterOfferSubmissionStatus $s) => $s->value,
            self::PENDING_OFFER_STATUSES,
        );

        $recent = RecruiterProfileAssignment::query()
            ->where('recruiter_organization_id', $orgId)
            ->where('status', RecruiterAssignmentStatus::Active->value)
            ->with([...UserPersonName::withProfile('candidate')])
            ->latest('assigned_at')
            ->limit(5)
            ->get();

        return [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'status' => $organization->status instanceof \BackedEnum
                    ? $organization->status->value
                    : (string) $organization->status,
                'portal_host' => $organization->portal_host,
            ],
            'stats' => [
                'assignments_active' => RecruiterProfileAssignment::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->where('status', RecruiterAssignmentStatus::Active->value)
                    ->count(),
                'offers_pending' => RecruiterOfferSubmission::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->whereIn('status', $pendingOfferValues)
                    ->count(),
                'offers_total' => RecruiterOfferSubmission::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->count(),
            ],
            'recent_assignments' => $recent->map(function (RecruiterProfileAssignment $assignment): array {
                $candidate = $assignment->candidate;
                $name = null;
                if ($candidate !== null) {
                    $name = trim(
                        UserPersonName::firstName($candidate).' '.UserPersonName::lastName($candidate),
                    );
                    if ($name === '') {
                        $name = (string) ($candidate->name ?? '');
                    }
                }

                return [
                    'id' => $assignment->id,
                    'candidate_user_id' => $assignment->candidate_user_id,
                    'candidate_name' => $name,
                    'candidate_email' => $candidate?->email,
                    'assigned_at' => $assignment->assigned_at?->toIso8601String(),
                    'visible_sections' => $assignment->resolvedVisibleSections(),
                ];
            })->values()->all(),
        ];
    }
}
