<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Core\Domain\Recruiter\Support\RecruiterOfferPayloadFields;

final class CreateRecruiterOfferSubmissionAction
{
    public function __construct(private readonly RecruiterAccess $access) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(RecruiterOrganization $organization, User $recruiter, array $payload): RecruiterOfferSubmission
    {
        if ($organization->status?->value !== 'active') {
            throw new \InvalidArgumentException(__('Organisation recruteur non active.'));
        }

        if (! $this->access->belongsToOrganization($recruiter, $organization->id)) {
            throw new \InvalidArgumentException(__('Accès organisation refusé.'));
        }

        $safePayload = RecruiterOfferPayloadFields::only($payload, RecruiterOfferPayloadFields::RECRUITER_KEYS);

        return RecruiterOfferSubmission::query()->create([
            'recruiter_organization_id' => $organization->id,
            'submitted_by_user_id' => $recruiter->id,
            'status' => RecruiterOfferSubmissionStatus::Draft,
            'payload' => $safePayload,
        ]);
    }
}
