<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRecruiterSubmissionRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone_number1' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array{email: string, name?: string|null, phone_number1?: string|null}
     */
    public function candidatePayload(): array
    {
        return $this->validated();
    }
}
