<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Resources;

use App\Core\Domain\Partner\Models\PartnerCohortRequiredDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnerCohortRequiredDocument */
final class PartnerCohortRequiredDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type_code' => $this->document_type_code,
            'is_mandatory' => $this->is_mandatory,
            'sort_order' => $this->sort_order,
            'label_override' => $this->label_override,
        ];
    }
}
