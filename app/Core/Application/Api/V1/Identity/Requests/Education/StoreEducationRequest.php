<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Education;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\Education;
use Illuminate\Foundation\Http\FormRequest;

final class StoreEducationRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return Education::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'education_level_id' => ['nullable', 'integer', 'exists:education_levels,id'],

            'document_id' => ['nullable', 'integer', 'exists:user_documents,id'],

            'degree' => ['required', 'string', 'max:255'],

            'institution_name' => ['required', 'string', 'max:255'],

            'field_of_study' => ['nullable', 'string', 'max:255'],

            'country_id' => ['nullable', 'integer', 'exists:countries,id'],

            'residence_city_id' => ['nullable', 'integer', 'exists:cities,id'],

            'start_date' => ['required', 'date'],

            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],

            'is_current' => ['sometimes', 'boolean'],

            'grade' => ['nullable', 'string', 'max:100'],

        ];

    }

}
