<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Core\Domain\Recruiter\Support\RecruiterProfileRequestCriteria;

final class CreateRecruiterProfileRequestAction
{
    public function __construct(private readonly RecruiterAccess $access) {}

    /**
     * @param  array<string, mixed>  $criteria
     */
    public function execute(
        RecruiterOrganization $organization,
        User $recruiter,
        string $title,
        array $criteria,
        int $quantityNeeded = 10,
        ?string $note = null,
    ): RecruiterProfileRequest {
        if ($organization->status?->value !== 'active') {
            throw new \InvalidArgumentException(__('Organisation recruteur non active.'));
        }

        if (! $this->access->belongsToOrganization($recruiter, $organization->id)) {
            throw new \InvalidArgumentException(__('Accès organisation refusé.'));
        }

        return RecruiterProfileRequest::query()->create([
            'recruiter_organization_id' => $organization->id,
            'submitted_by_user_id' => $recruiter->id,
            'status' => RecruiterProfileRequestStatus::Draft,
            'title' => $title,
            'criteria' => $criteria,
            'quantity_needed' => max(1, min(RecruiterProfileRequestCriteria::MAX_QUANTITY, $quantityNeeded)),
            'note' => $note,
        ]);
    }
}
