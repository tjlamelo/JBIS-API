<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LegalDocument */
class LegalDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $admin = $request->is('api/v1/identity/admin/legal-documents*');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'version' => $this->version,
            'title' => $admin ? $this->getTranslations('title') : $this->getTranslation('title', $locale),
            'content' => $admin ? $this->getTranslations('content') : $this->getTranslation('content', $locale),
            'summary' => $this->summary,
            'effective_at' => $this->effective_at?->toIso8601String(),
            'is_current' => (bool) $this->is_current,
            'requires_reacceptance' => (bool) $this->requires_reacceptance,
            'published_by' => $this->published_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
