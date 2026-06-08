<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\UserPersonName;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterDashboardStatsQuery
{
    private const ACTIVE_SUBMISSION_STATUSES = [
        RecruiterSubmissionStatus::Draft,
        RecruiterSubmissionStatus::Submitted,
        RecruiterSubmissionStatus::InReview,
        RecruiterSubmissionStatus::NeedsChanges,
    ];

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
                    'submissions_active' => 0,
                    'submissions_total' => 0,
                    'assignments_active' => 0,
                    'offers_pending' => 0,
                ],
                'recent_submissions' => [],
            ];
        }

        $orgId = (int) $organization->id;
        $activeStatusValues = array_map(
            static fn (RecruiterSubmissionStatus $s) => $s->value,
            self::ACTIVE_SUBMISSION_STATUSES,
        );
        $pendingOfferValues = array_map(
            static fn (RecruiterOfferSubmissionStatus $s) => $s->value,
            self::PENDING_OFFER_STATUSES,
        );

        $recent = RecruiterProfileSubmission::query()
            ->where('recruiter_organization_id', $orgId)
            ->with([...UserPersonName::withProfile('candidate')])
            ->latest('updated_at')
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
                'submissions_active' => RecruiterProfileSubmission::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->whereIn('status', $activeStatusValues)
                    ->count(),
                'submissions_total' => RecruiterProfileSubmission::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->count(),
                'assignments_active' => RecruiterProfileAssignment::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->where('status', RecruiterAssignmentStatus::Active->value)
                    ->count(),
                'offers_pending' => RecruiterOfferSubmission::query()
                    ->where('recruiter_organization_id', $orgId)
                    ->whereIn('status', $pendingOfferValues)
                    ->count(),
            ],
            'recent_submissions' => $recent->map(function (RecruiterProfileSubmission $submission): array {
                $candidate = $submission->candidate;
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
                    'id' => $submission->id,
                    'status' => $submission->status instanceof \BackedEnum
                        ? $submission->status->value
                        : (string) $submission->status,
                    'candidate_name' => $name,
                    'candidate_email' => $candidate?->email,
                    'submitted_at' => $submission->submitted_at?->toIso8601String(),
                    'updated_at' => $submission->updated_at?->toIso8601String(),
                ];
            })->values()->all(),
        ];
    }
}
