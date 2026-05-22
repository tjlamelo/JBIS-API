<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowDto;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProcessFlow::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->flowRules(), $this->sectionRules());
    }

    public function toDto(): ProcessFlowDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($this->all());

        return ProcessFlowDto::fromArray($validated);
    }

    /**
     * @return array<string, mixed>
     */
    private function flowRules(): array
    {
        $statuses = array_map(static fn (ProcessFlowStatus $s) => $s->value, ProcessFlowStatus::cases());

        return [
            'name' => ['required', 'array'],
            'name.fr' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'flow_group_id' => ['nullable', 'uuid'],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in($statuses)],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'estimated_duration_days' => ['nullable', 'integer', 'min:0'],
            'total_procedure_fees' => ['nullable', 'numeric', 'min:0'],
            'file_opening_fee' => ['nullable', 'numeric', 'min:0'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sectionRules(): array
    {
        $stepTypes = array_map(static fn (ProcessStepType $t) => $t->value, ProcessStepType::cases());
        $paymentTypes = array_map(static fn (PaymentType $t) => $t->value, PaymentType::cases());
        $parties = array_map(static fn (ResponsibleParty $p) => $p->value, ResponsibleParty::cases());

        return [
            'sections' => ['nullable', 'array'],
            'sections.*.key' => ['required_with:sections', 'string', 'max:64'],
            'sections.*.title' => ['required_with:sections', 'array'],
            'sections.*.title.fr' => ['required_with:sections.*.title', 'string', 'max:255'],
            'sections.*.title.en' => ['nullable', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'array'],
            'sections.*.section_order' => ['nullable', 'integer', 'min:1'],
            'sections.*.color' => ['nullable', 'string', 'max:16'],
            'sections.*.icon' => ['nullable', 'string', 'max:64'],
            'sections.*.visible_after_section_key' => ['nullable', 'string', 'max:64'],
            'sections.*.steps' => ['nullable', 'array'],
            'sections.*.steps.*.step_type' => ['nullable', 'string', Rule::in($stepTypes)],
            'sections.*.steps.*.payment_type' => ['nullable', 'string', Rule::in($paymentTypes)],
            'sections.*.steps.*.responsible_party' => ['nullable', 'string', Rule::in($parties)],
            'sections.*.steps.*.title' => ['required_with:sections.*.steps', 'array'],
            'sections.*.steps.*.title.fr' => ['required_with:sections.*.steps.*.title', 'string', 'max:255'],
            'sections.*.steps.*.description' => ['nullable', 'array'],
            'sections.*.steps.*.step_order' => ['nullable', 'integer', 'min:1'],
            'sections.*.steps.*.default_amount' => ['nullable', 'numeric', 'min:0'],
            'sections.*.steps.*.requires_documents' => ['nullable', 'boolean'],
            'sections.*.steps.*.document_type_ids' => ['nullable', 'array'],
            'sections.*.steps.*.document_type_ids.*' => ['integer', 'exists:document_types,id'],
            'sections.*.steps.*.is_blocking' => ['nullable', 'boolean'],
            'sections.*.steps.*.is_required' => ['nullable', 'boolean'],
        ];
    }
}
