<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentDocumentStatus;
use App\Core\Domain\Partner\Models\PartnerCohortStudent;

final class SyncPartnerCohortStudentChecklistAction
{
    public function execute(PartnerCohortStudent $student): PartnerCohortStudent
    {
        if ($student->student_user_id === null) {
            return $student;
        }

        $student->loadMissing('documents');

        foreach ($student->documents as $checklistItem) {
            $userDocument = UserDocument::query()
                ->where('user_id', $student->student_user_id)
                ->whereHas('documentType', fn ($q) => $q->where('code', $checklistItem->document_type_code))
                ->latest('updated_at')
                ->first();

            if ($userDocument === null) {
                if ($checklistItem->status !== PartnerCohortStudentDocumentStatus::Missing) {
                    $checklistItem->update([
                        'user_document_id' => null,
                        'status' => PartnerCohortStudentDocumentStatus::Missing,
                        'validated_by' => null,
                        'validated_at' => null,
                    ]);
                }

                continue;
            }

            $status = match ($userDocument->status) {
                UserDocumentStatus::Approved => PartnerCohortStudentDocumentStatus::Validated,
                UserDocumentStatus::Rejected => PartnerCohortStudentDocumentStatus::Rejected,
                default => PartnerCohortStudentDocumentStatus::Uploaded,
            };

            $checklistItem->update([
                'user_document_id' => $userDocument->id,
                'status' => $status,
                'validated_by' => $status === PartnerCohortStudentDocumentStatus::Validated ? $userDocument->validated_by : null,
                'validated_at' => $status === PartnerCohortStudentDocumentStatus::Validated ? $userDocument->validated_at : null,
            ]);
        }

        return $student->fresh(['documents.userDocument', 'student:id,name,email']);
    }
}
