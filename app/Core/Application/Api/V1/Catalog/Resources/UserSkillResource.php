<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources;

use App\Core\Domain\Identity\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserSkill */
final class UserSkillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'skill_id' => $this->skill_id,
            'years_of_experience' => $this->years_of_experience,
            'level' => $this->level,
            'skill' => $this->whenLoaded('skill', fn () => [
                'id' => $this->skill?->id,
                'name' => $this->skill?->name ?? null,
                'slug' => $this->skill?->slug ?? null,
                'category_id' => $this->skill?->category_id,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
