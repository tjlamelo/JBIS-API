<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowDto;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $flow = $this->route('processFlow');

        return $flow instanceof ProcessFlow
            && ($this->user()?->can('update', $flow) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $store = new StoreProcessFlowRequest;
        $rules = $store->rules();

        foreach (['name', 'name.fr', 'sections', 'sections.*.key', 'sections.*.title', 'sections.*.steps.*.title'] as $key) {
            if (isset($rules[$key]) && is_array($rules[$key])) {
                $rules[$key] = array_merge(['sometimes'], array_filter(
                    $rules[$key],
                    static fn ($r) => $r !== 'required' && $r !== 'required_with:sections'
                ));
            }
        }

        return $rules;
    }

    public function toDto(): ProcessFlowDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($this->all());

        return ProcessFlowDto::fromArray($validated);
    }
}
