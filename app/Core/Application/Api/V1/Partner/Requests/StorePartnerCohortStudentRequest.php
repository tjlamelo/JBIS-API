<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePartnerCohortStudentRequest extends FormRequest
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
            'invited_name' => ['required', 'string', 'max:255'],
            'invited_email' => ['nullable', 'email', 'max:255'],
            'student_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'partner_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
