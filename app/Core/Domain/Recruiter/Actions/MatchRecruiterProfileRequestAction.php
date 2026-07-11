<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Queries\AdminUserIdsFromFiltersQuery;
use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterProfileRequestCriteria;

final class MatchRecruiterProfileRequestAction
{
    public function __construct(
        private readonly AdminUserIdsFromFiltersQuery $userIdsFromFilters,
    ) {}

    public function execute(RecruiterProfileRequest $request): RecruiterProfileRequest
    {
        $criteria = is_array($request->criteria) ? $request->criteria : [];
        $filters = RecruiterProfileRequestCriteria::toSearchFilters($criteria);
        $limit = max(1, min(
            RecruiterProfileRequestCriteria::MAX_QUANTITY,
            (int) ($request->quantity_needed ?: RecruiterProfileRequestCriteria::MAX_QUANTITY),
        ));

        $ids = $this->userIdsFromFilters->collect($filters, onlyApproved: true, limit: $limit)->all();

        $request->matched_candidate_ids = $ids;
        $request->matched_count = count($ids);

        if ($request->status === RecruiterProfileRequestStatus::Submitted && $request->matched_count > 0) {
            $request->status = RecruiterProfileRequestStatus::Matched;
        }

        $request->save();

        return $request->fresh(['organization', 'submittedBy:id,name,email']);
    }
}
