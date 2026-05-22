<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use Illuminate\Support\Facades\Date;

final class ValidateUserDocumentAction
{
    public function execute(
        UserDocument $document,
        UserDocumentStatus $status,
        int $validatorId,
        ?string $rejectionReason = null,
    ): UserDocument {
        $document->status = $status;
        $document->validated_by = $validatorId;
        $document->validated_at = Date::now();
        $document->rejection_reason = $status === UserDocumentStatus::Rejected
            ? $rejectionReason
            : null;

        $document->save();

        return $document->fresh(['issuingCountry', 'user', 'validator']);
    }
}
