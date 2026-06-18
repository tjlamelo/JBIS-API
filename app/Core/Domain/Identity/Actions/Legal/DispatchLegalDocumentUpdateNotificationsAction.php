<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Legal;

use App\Core\Domain\Identity\Jobs\NotifyUsersOfLegalDocumentUpdateJob;
use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ConsentType;

final class DispatchLegalDocumentUpdateNotificationsAction
{
    private const CHUNK_SIZE = 100;

    public function execute(LegalDocument $document): int
    {
        if (! $document->requires_reacceptance) {
            return 0;
        }

        if ($document->type === ConsentType::MARKETING) {
            return 0;
        }

        $userIds = User::query()
            ->where('active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($userIds === []) {
            return 0;
        }

        foreach (array_chunk($userIds, self::CHUNK_SIZE) as $chunk) {
            NotifyUsersOfLegalDocumentUpdateJob::dispatch($document->id, $chunk);
        }

        return count($userIds);
    }
}
