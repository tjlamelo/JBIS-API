<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\UserNote;
use App\Core\Domain\Identity\Support\UserPersonName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserNote */
final class UserNoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'author_id' => $this->author_id,
            'content' => $this->content,
            'is_private' => $this->is_private,
            'author' => $this->whenLoaded('author', fn () => $this->author
                ? UserPersonName::toContactArray($this->author)
                : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
