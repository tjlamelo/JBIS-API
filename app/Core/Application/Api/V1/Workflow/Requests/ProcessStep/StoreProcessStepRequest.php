<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Requests\ProcessStep;

use App\Core\Domain\Workflow\DTOs\ProcessStep\ProcessStepDto;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProcessStep::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stepTypes = array_map(static fn (ProcessStepType $t) => $t->value, ProcessStepType::cases());

        return [
            'process_flow_id' => ['required', 'integer', 'exists:process_flows,id'],
            'step_order' => ['nullable', 'integer', 'min:1'],
            'section_key' => ['nullable', 'string', 'max:64'],
            'section' => ['nullable', 'array'],
            'section.fr' => ['nullable', 'string', 'max:255'],
            'section.en' => ['nullable', 'string', 'max:255'],
            'step_type' => ['nullable', 'string', Rule::in($stepTypes)],
            'title' => ['required', 'array'],
            'title.fr' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'requires_documents' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): ProcessStepDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($this->all());

        return ProcessStepDto::fromArray($validated);
    }
}
