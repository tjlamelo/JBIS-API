<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Language */
final class UserLanguageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'language_id' => $this->language_id,
            'language_level_id' => $this->language_level_id,
            'is_approved' => $this->is_approved,
            'approved_by' => $this->approved_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'language' => $this->whenLoaded('language', fn () => [
                'id' => $this->language?->id,
                'code' => $this->language?->code,
                'name' => $this->language?->name ?? null,
            ]),
            'language_level' => $this->whenLoaded('languageLevel', fn () => [
                'id' => $this->languageLevel?->id,
                'code' => $this->languageLevel?->code ?? null,
                'name' => $this->languageLevel?->name ?? null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
