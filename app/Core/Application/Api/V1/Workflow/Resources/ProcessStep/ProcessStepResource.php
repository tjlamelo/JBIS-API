<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Resources\ProcessStep;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'process_flow_id' => $this->process_flow_id,
            'process_flow_section_id' => $this->process_flow_section_id,
            'step_type' => $this->step_type,
            'payment_type' => $this->payment_type,
            'responsible_party' => $this->responsible_party,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'internal_note' => $this->internal_note,
            'step_order' => $this->step_order,
            'is_blocking' => (bool) $this->is_blocking,
            'is_required' => (bool) $this->is_required,
            'default_amount' => $this->default_amount,
            'accepted_banks' => $this->accepted_banks,
            'requires_documents' => (bool) $this->requires_documents,
            'document_type_ids' => $this->document_type_ids ?? [],
            'estimated_duration_days' => $this->estimated_duration_days,
            'sla_alert_days' => $this->sla_alert_days,
        ];
    }
}
