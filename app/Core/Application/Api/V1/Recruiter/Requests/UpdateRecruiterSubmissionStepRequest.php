<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRecruiterSubmissionStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:50'],
            'last_name' => ['sometimes', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'date'],
            'place_of_birth' => ['sometimes', 'nullable', 'string', 'max:25'],
            'nationality_country_id' => ['sometimes', 'nullable', 'integer', 'exists:countries,id'],
            'residence_city_id' => ['sometimes', 'nullable', 'integer', 'exists:cities,id'],
            'gender' => ['sometimes', 'nullable', Rule::in(['M', 'F'])],
            'marital_status' => ['sometimes', 'nullable', Rule::in(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'])],
            'number_of_children' => ['sometimes', 'integer', 'min:0'],
            'address' => ['sometimes', 'nullable', 'string', 'max:50'],
            'phone_number2' => ['sometimes', 'nullable', 'string', 'max:20'],
            'phone_number3' => ['sometimes', 'nullable', 'string', 'max:20'],
            'total_years_of_experience' => ['sometimes', 'integer', 'min:0'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'pictures' => ['sometimes', 'array'],
        ];
    }
}
