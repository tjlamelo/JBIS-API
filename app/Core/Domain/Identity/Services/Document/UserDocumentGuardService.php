<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Identity\Exceptions\Document\UserDocumentLockedException;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;

final class UserDocumentGuardService
{
    public function assertCandidateCanMutate(UserDocument $document, User $actor): void
    {
        if ($this->actorIsStaff($actor, $document)) {
            return;
        }

        if ($this->isValidatedByStaff($document)) {
            throw UserDocumentLockedException::validatedByStaff();
        }

        if ($this->isLinkedToActiveApplication($document)) {
            throw UserDocumentLockedException::forActiveApplication();
        }
    }

    /**
     * @return array{locked: bool, reason: string|null}
     */
    public function lockStateForCandidate(UserDocument $document): array
    {
        if ($this->isValidatedByStaff($document)) {
            return [
                'locked' => true,
                'reason' => 'validated_by_staff',
            ];
        }

        if ($this->isLinkedToActiveApplication($document)) {
            return [
                'locked' => true,
                'reason' => 'active_application',
            ];
        }

        return [
            'locked' => false,
            'reason' => null,
        ];
    }

    public function isLinkedToActiveApplication(UserDocument $document): bool
    {
        return ApplicationDocument::query()
            ->where('user_document_id', $document->id)
            ->whereHas('application', function ($query): void {
                $query->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::InProgress->value,
                ]);
            })
            ->exists();
    }

    private function isValidatedByStaff(UserDocument $document): bool
    {
        if ($document->validated_by === null || $document->validated_at === null) {
            return false;
        }

        $status = $document->status instanceof UserDocumentStatus
            ? $document->status
            : UserDocumentStatus::tryFrom((string) $document->status);

        return $status === UserDocumentStatus::Approved;
    }

    private function actorIsStaff(User $actor, UserDocument $document): bool
    {
        return (int) $actor->id !== (int) $document->user_id
            && $actor->can('update', $document);
    }
}
