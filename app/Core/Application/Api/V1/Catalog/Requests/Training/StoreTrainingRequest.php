<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Training;

use App\Core\Domain\Catalog\DTOs\Training\TrainingDto;
use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Catalog\States\TrainingDeliveryMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Training::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $modes = array_map(static fn (TrainingDeliveryMode $m) => $m->value, TrainingDeliveryMode::cases());

        return [
            'domain' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_hours' => ['nullable', 'integer', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:0'],
            'mode' => ['nullable', 'string', Rule::in($modes)],
            'location' => ['nullable', 'string', 'max:500'],
            'prerequisites' => ['nullable', 'string'],
            'is_certified' => ['nullable', 'boolean'],
            'certificate_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): TrainingDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($validated);

        return TrainingDto::fromArray($validated);
    }
}
