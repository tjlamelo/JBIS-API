<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Requests\ProcessStep;

use App\Core\Domain\Workflow\DTOs\ProcessStep\ProcessStepDto;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcessStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        $step = $this->route('processStep');

        return $step instanceof ProcessStep
            && ($this->user()?->can('update', $step) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stepTypes = array_map(static fn (ProcessStepType $t) => $t->value, ProcessStepType::cases());

        return [
            'process_flow_id' => ['sometimes', 'integer', 'exists:process_flows,id'],
            'step_order' => ['sometimes', 'integer', 'min:1'],
            'section_key' => ['sometimes', 'nullable', 'string', 'max:64'],
            'section' => ['sometimes', 'nullable', 'array'],
            'section.fr' => ['nullable', 'string', 'max:255'],
            'section.en' => ['nullable', 'string', 'max:255'],
            'step_type' => ['sometimes', 'string', Rule::in($stepTypes)],
            'title' => ['sometimes', 'array'],
            'title.fr' => ['required_with:title', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'default_amount' => ['sometimes', 'numeric', 'min:0'],
            'requires_documents' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): ProcessStepDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($this->all());

        return ProcessStepDto::fromArray($validated);
    }
}
