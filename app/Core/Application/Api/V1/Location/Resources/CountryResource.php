<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Location\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name, // Retourne la string selon la locale actuelle
            'code'       => $this->code,
            'flag'       => $this->flag,
            'phone_code' => $this->phone_code,
            // On ne renvoie pas 'is_active' au front public, c'est implicite
        ];
    }
}