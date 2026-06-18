<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Education;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEducationRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'education';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'education_level_id' => ['sometimes', 'nullable', 'integer', 'exists:education_levels,id'],

            'document_id' => ['sometimes', 'nullable', 'integer', 'exists:user_documents,id'],

            'degree' => ['sometimes', 'string', 'max:255'],

            'institution_name' => ['sometimes', 'string', 'max:255'],

            'field_of_study' => ['sometimes', 'nullable', 'string', 'max:255'],

            'country_id' => ['sometimes', 'nullable', 'integer', 'exists:countries,id'],

            'residence_city' => ['sometimes', 'nullable', 'string', 'max:120'],

            'start_date' => ['sometimes', 'date'],

            'end_date' => ['sometimes', 'nullable', 'date'],

            'is_current' => ['sometimes', 'boolean'],

            'grade' => ['sometimes', 'nullable', 'string', 'max:100'],

        ];

    }

}
