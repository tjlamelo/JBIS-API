<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRecruiterAssignmentFeedbackRequest extends FormRequest
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
            'feedback_status' => ['required', 'string', Rule::in([
                'shortlisted',
                'rejected',
                'contacted',
                'interview_scheduled',
                'hired',
            ])],
            'feedback_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
