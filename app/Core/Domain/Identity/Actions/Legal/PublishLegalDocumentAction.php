<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Legal;

use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class PublishLegalDocumentAction
{
    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $content
     */
    public function execute(
        string $type,
        string $version,
        array $title,
        array $content,
        ?string $summary,
        bool $requiresReacceptance,
        ?User $publisher = null,
    ): LegalDocument {
        return DB::transaction(function () use (
            $type,
            $version,
            $title,
            $content,
            $summary,
            $requiresReacceptance,
            $publisher,
        ): LegalDocument {
            LegalDocument::query()
                ->where('type', $type)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $document = LegalDocument::query()->firstOrNew([
                'type' => $type,
                'version' => $version,
            ]);

            $document->fill([
                'summary' => $summary,
                'effective_at' => now(),
                'is_current' => true,
                'requires_reacceptance' => $requiresReacceptance,
                'published_by' => $publisher?->id,
            ]);

            $document->setTranslations('title', $title);
            $document->setTranslations('content', $content);
            $document->save();

            return $document->refresh();
        });
    }
}
