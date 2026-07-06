<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;
use App\Core\Domain\Identity\Services\Document\UserDocumentGuardService;

final class DeleteUserDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storage,
        private readonly UserDocumentGuardService $documentGuard,
    ) {}

    public function execute(UserDocument $document, ?User $actor = null): void
    {
        if ($actor !== null) {
            $this->documentGuard->assertCandidateCanMutate($document, $actor);
        }

        $this->purgeCvDerivedSections($document);

        $this->storage->delete($document->file_path);
        $document->delete();
    }

    private function purgeCvDerivedSections(UserDocument $document): void
    {
        Education::query()->where('document_id', $document->id)->delete();
        Experience::query()->where('document_id', $document->id)->delete();
        Certification::query()->where('document_id', $document->id)->delete();
        UserInternship::query()->where('certificate_document_id', $document->id)->delete();
    }
}
