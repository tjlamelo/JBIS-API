<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ValidateApplicationStepRequest extends FormRequest
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
            'documents_validated' => ['sometimes', 'boolean'],
            'interview_passed' => ['sometimes', 'nullable', 'boolean'],
            'is_signed' => ['sometimes', 'boolean'],
        ];
    }
}
