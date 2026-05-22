<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use Illuminate\Support\Carbon;

final class ReviewApplicationDocumentAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    /**
     * @param  'PENDING'|'APPROVED'|'REJECTED'|'REVISION_REQUIRED'  $status
     */
    public function execute(
        ApplicationDocument $document,
        string $status,
        int $staffUserId,
        ?string $adminNotes = null,
    ): ApplicationDocument {
        $document->update([
            'status' => $status,
            'admin_notes' => $adminNotes,
            'reviewed_by' => $staffUserId,
            'reviewed_at' => Carbon::now(),
        ]);

        $this->activityLogger->log(
            (int) $document->application_id,
            ApplicationActivityLogger::ACTION_DOCUMENT_REVIEWED,
            $document->application_step_id,
            $staffUserId,
            [
                'application_document_id' => $document->id,
                'status' => $status,
            ],
        );

        return $document->fresh(['userDocument.documentType', 'reviewer:id,first_name,last_name']);
    }
}
