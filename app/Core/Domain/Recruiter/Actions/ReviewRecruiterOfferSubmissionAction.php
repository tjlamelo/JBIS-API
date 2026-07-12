<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Catalog\Actions\Offer\CreateOfferAction;
use App\Core\Domain\Catalog\DTOs\Offer\OfferDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterOfferPayloadFields;

final class ReviewRecruiterOfferSubmissionAction
{
    public function __construct(private readonly CreateOfferAction $createOffer) {}

    /**
     * @param  array{
     *     decision: string,
     *     staff_note?: string|null,
     *     rejection_reason?: string|null,
     *     offer_payload?: array<string, mixed>|null
     * }  $payload
     */
    public function execute(RecruiterOfferSubmission $submission, User $reviewer, array $payload): RecruiterOfferSubmission
    {
        $decision = (string) ($payload['decision'] ?? '');
        $status = match ($decision) {
            'approve' => RecruiterOfferSubmissionStatus::Approved,
            'reject' => RecruiterOfferSubmissionStatus::Rejected,
            'needs_changes' => RecruiterOfferSubmissionStatus::NeedsChanges,
            'in_review' => RecruiterOfferSubmissionStatus::InReview,
            default => throw new \InvalidArgumentException(__('Décision de modération invalide.')),
        };

        if (isset($payload['offer_payload']) && is_array($payload['offer_payload'])) {
            $overlay = RecruiterOfferPayloadFields::only(
                $payload['offer_payload'],
                RecruiterOfferPayloadFields::staffAllowedKeys(),
            );
            $base = is_array($submission->payload) ? $submission->payload : [];
            $submission->payload = RecruiterOfferPayloadFields::merge($base, $overlay);
        }

        $submission->status = $status;
        $submission->reviewed_by = $reviewer->id;
        $submission->reviewed_at = now();
        $submission->staff_note = $payload['staff_note'] ?? null;
        $submission->rejection_reason = $payload['rejection_reason'] ?? null;

        if ($status === RecruiterOfferSubmissionStatus::Approved) {
            $offerPayload = is_array($submission->payload) ? $submission->payload : [];
            $offerPayload['status'] = 'PUBLISHED';
            $offerPayload['user_id'] = $submission->submitted_by_user_id;
            $offerPayload['company_id'] = $offerPayload['company_id'] ?? $submission->organization?->company_id;
            if (empty($offerPayload['published_at'])) {
                $offerPayload['published_at'] = now()->toDateTimeString();
            }

            $dto = OfferDto::fromArray($offerPayload);
            $offer = $this->createOffer->execute($dto);
            $submission->offer_id = $offer->id;
        }

        $submission->save();

        return $submission->fresh(['organization', 'submittedBy', 'offer', 'reviewer']);
    }
}
