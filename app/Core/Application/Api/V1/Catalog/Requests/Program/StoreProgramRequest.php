<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Program;

use App\Core\Domain\Catalog\DTOs\Program\ProgramDto;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\States\ProgramStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Program::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $statuses = array_map(static fn (ProgramStatus $s) => $s->value, ProgramStatus::cases());

        return [
            'name' => ['required', 'array'],
            'name.fr' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'slug' => ['nullable', 'array'],
            'slug.fr' => ['nullable', 'string', 'max:255'],
            'slug.en' => ['nullable', 'string', 'max:255'],

            'geographic_zone_id' => ['nullable', 'integer', 'exists:geographic_zones,id'],

            'procedure_duration' => ['nullable', 'integer', 'min:0'],
            'duration_unit' => ['nullable', 'string', 'max:20'],

            'age_min' => ['nullable', 'integer', 'min:0', 'max:150'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:150'],

            'is_featured' => ['nullable', 'boolean'],
            'is_urgent' => ['nullable', 'boolean'],
            'views_count' => ['nullable', 'integer', 'min:0'],

            'image_media' => ['nullable', 'array'],

            'status' => ['nullable', 'string', Rule::in($statuses)],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'published_at' => ['nullable', 'date'],

            'required_documents' => ['nullable', 'array'],
            'required_documents.*.required_document_id' => ['required_with:required_documents', 'integer', 'exists:required_documents,id'],
            'required_documents.*.is_mandatory' => ['nullable', 'boolean'],
            'required_documents.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'language_requirements' => ['nullable', 'array'],
            'language_requirements.*.language_id' => ['required_with:language_requirements', 'integer', 'exists:languages,id'],
            'language_requirements.*.is_mandatory' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $v): void {
            $min = $this->input('age_min');
            $max = $this->input('age_max');
            if ($min === null || $max === null || $min === '' || $max === '') {
                return;
            }
            if ((int) $max < (int) $min) {
                $v->errors()->add('age_max', __('L\'âge maximum doit être supérieur ou égal à l\'âge minimum.'));
            }
        });
    }

    public function toDto(): ProgramDto
    {
        $validated = $this->validated();
        $validated['user_id'] = $this->user()?->id;
        $validated['provided_keys'] = array_keys($this->all());

        return ProgramDto::fromArray($validated);
    }
}
