<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewRecruiterOfferSubmissionRequest extends FormRequest
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
            'offer_payload' => ['sometimes', 'array'],
            'offer_payload.trade_id' => ['sometimes', 'nullable', 'integer', 'exists:trades,id'],
            'offer_payload.description' => ['sometimes', 'nullable', 'array'],
            'offer_payload.description.fr' => ['sometimes', 'nullable', 'string'],
            'offer_payload.description.en' => ['sometimes', 'nullable', 'string'],
            'offer_payload.company_id' => ['sometimes', 'nullable', 'integer', 'exists:companies,id'],
            'offer_payload.contract_type_id' => ['sometimes', 'nullable', 'integer', 'exists:contract_types,id'],
            'offer_payload.country_id' => ['sometimes', 'nullable', 'integer', 'exists:countries,id'],
            'offer_payload.city_id' => ['sometimes', 'nullable', 'integer', 'exists:cities,id'],
            'offer_payload.salary_min' => ['sometimes', 'nullable', 'numeric'],
            'offer_payload.salary_max' => ['sometimes', 'nullable', 'numeric'],
            'offer_payload.work_mode' => ['sometimes', 'nullable', 'string', Rule::in(['on-site', 'hybrid', 'remote'])],
            'offer_payload.address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'offer_payload.currency' => ['sometimes', 'nullable', 'string', 'max:8'],
            'offer_payload.is_salary_public' => ['sometimes', 'boolean'],
            'offer_payload.is_company_public' => ['sometimes', 'boolean'],
            'offer_payload.allows_public_applications' => ['sometimes', 'boolean'],
            'offer_payload.available_positions' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:500'],
            'offer_payload.offer_type_id' => ['sometimes', 'nullable', 'integer', 'exists:offer_types,id'],
            'offer_payload.work_schedule_id' => ['sometimes', 'nullable', 'integer', 'exists:work_schedules,id'],
            'offer_payload.education_level_id' => ['sometimes', 'nullable', 'integer', 'exists:education_levels,id'],
            'offer_payload.program_id' => ['sometimes', 'nullable', 'integer', 'exists:programs,id'],
            'offer_payload.benefit_ids' => ['sometimes', 'array'],
            'offer_payload.benefit_ids.*' => ['integer'],
            'offer_payload.language_requirements' => ['sometimes', 'array'],
            'offer_payload.skill_requirements' => ['sometimes', 'array'],
            'offer_payload.required_documents' => ['sometimes', 'array'],
            'offer_payload.expiration_date' => ['sometimes', 'nullable', 'date'],
            'offer_payload.published_at' => ['sometimes', 'nullable', 'date'],
            'offer_payload.meta' => ['sometimes', 'nullable', 'array'],
            'offer_payload.language' => ['sometimes', 'nullable', 'string', 'max:16'],
        ];
    }
}
