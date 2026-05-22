<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources;

use App\Core\Domain\Identity\Models\UserTraining;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserTraining */
final class UserTrainingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'training_id' => $this->training_id,
            'training' => $this->whenLoaded('training', fn () => [
                'id' => $this->training?->id,
                'title' => $this->training?->title,
                'domain' => $this->training?->domain,
                'organization' => $this->training?->organization,
            ]),
            'status' => $this->status,
            'started_at' => $this->started_at?->toDateString(),
            'finished_at' => $this->finished_at?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
