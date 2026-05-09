<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequiredDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
// app/Core/Application/Api/V1/Candidacy/Resources/RequiredDocumentResource.php

public function toArray($request): array
{
    return [
        'id'            => $this->id,
        'name'          => $this->name,
        'slug'          => $this->slug,
        'type'          => $this->type,
        'description'   => $this->description,
        'template_path' => $this->template_path,
        
        // 🟢 Priorité à la table pivot (votre capture d'écran)
        'is_mandatory'  => $this->pivot 
            ? (bool) $this->pivot->is_mandatory 
            : (bool) $this->is_mandatory,

        'sort_order'    => $this->pivot ? (int) $this->pivot->sort_order : 0,
        
        'program_id'    => $this->program_id,
        'offer_id'      => $this->offer_id,
    ];
}
}