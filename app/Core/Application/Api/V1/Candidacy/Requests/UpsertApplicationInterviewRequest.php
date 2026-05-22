<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpsertApplicationInterviewRequest extends FormRequest
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
            'scheduled_date' => ['nullable', 'date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:480'],
            'interview_type' => ['nullable', Rule::in(['ONLINE', 'PHONE', 'ONSITE'])],
            'location' => ['nullable', 'string', 'max:500'],
            'interviewer_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['SCHEDULED', 'COMPLETED', 'CANCELLED', 'RESCHEDULED', 'NO_SHOW'])],
            'result' => ['nullable', Rule::in(['PASSED', 'FAILED', 'PENDING', 'WAITING_LIST'])],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
            'candidate_feedback' => ['nullable', 'string', 'max:10000'],
            'evaluation_criteria' => ['nullable', 'array'],
            'evaluation_criteria.*' => ['nullable'],
            'salary_offered' => ['nullable', 'numeric', 'min:0'],
            'salary_negotiated' => ['nullable', 'numeric', 'min:0'],
            'work_conditions_notes' => ['nullable', 'string', 'max:10000'],
            'interview_passed' => ['nullable', 'boolean'],
        ];
    }
}
