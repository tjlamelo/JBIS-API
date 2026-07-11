<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;

final class SubmitRecruiterProfileRequestAction
{
    public function __construct(
        private readonly MatchRecruiterProfileRequestAction $matchRequest,
    ) {}

    public function execute(RecruiterProfileRequest $request): RecruiterProfileRequest
    {
        if (! $request->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette demande ne peut pas être soumise.'));
        }

        $criteria = is_array($request->criteria) ? $request->criteria : [];
        if (empty($criteria['trade_ids']) || ! is_array($criteria['trade_ids'])) {
            throw new \InvalidArgumentException(__('Au moins un métier est requis.'));
        }

        $request->status = RecruiterProfileRequestStatus::Submitted;
        $request->submitted_at = now();
        $request->rejection_reason = null;
        $request->save();

        return $this->matchRequest->execute($request);
    }
}
