<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewRecruiterSubmissionRequest extends FormRequest
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
            'decision' => ['required', Rule::in(['approve', 'reject', 'needs_changes', 'in_review'])],
            'staff_note' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
