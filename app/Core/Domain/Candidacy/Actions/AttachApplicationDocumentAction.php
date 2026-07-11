<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Exceptions\ApplicationDocumentException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Support\Facades\DB;

final class AttachApplicationDocumentAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    public function execute(
        Application $application,
        ApplicationStep $step,
        User $user,
        int $userDocumentId,
    ): ApplicationDocument {
        if ($step->application_id !== $application->id) {
            throw ApplicationDocumentException::stepMismatch();
        }

        if (! $step->requires_documents) {
            throw ApplicationDocumentException::stepDoesNotRequireDocuments();
        }

        $userDocument = UserDocument::query()
            ->whereKey($userDocumentId)
            ->where('user_id', $user->id)
            ->first();

        if ($userDocument === null) {
            throw ApplicationDocumentException::userDocumentNotFound();
        }

        $allowedTypeIds = $step->document_type_ids ?? [];
        if ($allowedTypeIds !== [] && ! in_array((int) $userDocument->document_type_id, $allowedTypeIds, true)) {
            throw ApplicationDocumentException::documentTypeNotAllowed();
        }

        return DB::transaction(function () use ($application, $step, $user, $userDocumentId): ApplicationDocument {
            $doc = ApplicationDocument::query()->updateOrCreate(
                [
                    'application_id' => $application->id,
                    'user_document_id' => $userDocumentId,
                ],
                [
                    'application_step_id' => $step->id,
                    'status' => 'PENDING',
                    'admin_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ],
            );

            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_DOCUMENT_ATTACHED,
                $step->id,
                (int) $user->id,
                ['application_document_id' => $doc->id, 'user_document_id' => $userDocumentId],
            );

            return $doc->fresh(['userDocument.documentType']);
        });
    }
}
