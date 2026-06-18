<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Offer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // Traduit automatiquement via HasTranslations
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
