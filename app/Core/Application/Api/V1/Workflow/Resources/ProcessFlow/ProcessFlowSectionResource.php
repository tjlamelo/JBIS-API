<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Resources\ProcessFlow;

use App\Core\Application\Api\V1\Workflow\Resources\ProcessStep\ProcessStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessFlowSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'process_flow_id' => $this->process_flow_id,
            'key' => $this->key,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'section_order' => $this->section_order,
            'color' => $this->color,
            'icon' => $this->icon,
            'visible_after_section_key' => $this->visible_after_section_key,
            'steps' => ProcessStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
