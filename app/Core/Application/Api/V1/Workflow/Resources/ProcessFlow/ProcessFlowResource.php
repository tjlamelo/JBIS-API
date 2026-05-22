<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Resources\ProcessFlow;

use App\Core\Application\Api\V1\Workflow\Resources\ProcessStep\ProcessStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessFlowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = is_object($status) && property_exists($status, 'value')
            ? $status->value
            : (string) $status;

        return [
            'id' => $this->id,
            'flow_group_id' => $this->flow_group_id,
            'version' => $this->version,
            'status' => $statusValue,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'program_id' => $this->program_id,
            'offer_id' => $this->offer_id,
            'country_id' => $this->country_id,
            'estimated_duration_days' => $this->estimated_duration_days,
            'total_procedure_fees' => $this->total_procedure_fees,
            'file_opening_fee' => $this->file_opening_fee,
            'internal_notes' => $this->internal_notes,
            'program' => $this->whenLoaded('program', fn () => [
                'id' => $this->program?->id,
                'name' => $this->program?->getTranslations('name'),
            ]),
            'offer' => $this->whenLoaded('offer', fn () => [
                'id' => $this->offer?->id,
                'title' => $this->offer?->getTranslations('title'),
            ]),
            'country' => $this->whenLoaded('country', fn () => [
                'id' => $this->country?->id,
                'name' => $this->country?->getTranslations('name'),
            ]),
            'sections' => ProcessFlowSectionResource::collection($this->whenLoaded('sections')),
            'steps' => ProcessStepResource::collection($this->whenLoaded('steps')),
            'steps_count' => $this->whenCounted('steps'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
