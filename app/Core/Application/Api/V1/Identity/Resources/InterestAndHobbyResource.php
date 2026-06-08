<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\InterestAndHobby;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InterestAndHobby */
final class InterestAndHobbyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
