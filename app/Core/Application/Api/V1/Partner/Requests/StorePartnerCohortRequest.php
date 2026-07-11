<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePartnerCohortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:32'],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'expected_student_count' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
