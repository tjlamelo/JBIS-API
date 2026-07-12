<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Archive */
final class ArchiveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'uploaded_by' => $this->uploaded_by,
            'related_user_id' => $this->related_user_id,
            'original_name' => $this->original_name,
            'file_type' => $this->file_type,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'readable_size' => $this->readable_size,
            'category' => $this->category,
            'description' => $this->description,
            'disk' => $this->disk,
            'is_public' => $this->is_public,
            'url' => $this->url,
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader ? [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
                'email' => $this->uploader->email,
            ] : null),
            'related_user' => $this->whenLoaded('relatedUser', fn () => $this->relatedUser ? [
                'id' => $this->relatedUser->id,
                'name' => $this->relatedUser->name,
                'email' => $this->relatedUser->email,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
