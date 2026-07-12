<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRecruiterOfferSubmissionRequest extends FormRequest
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
            'trade_id' => ['required', 'integer', 'exists:trades,id'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'contract_type_id' => ['nullable', 'integer', 'exists:contract_types,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'salary_min' => ['nullable', 'numeric'],
            'salary_max' => ['nullable', 'numeric'],
            'work_mode' => ['nullable', 'string', Rule::in(['on-site', 'hybrid', 'remote'])],
            'skill_requirements' => ['nullable', 'array'],
            'skill_requirements.*.skill_id' => ['required', 'integer', 'exists:skills,id'],
            'skill_requirements.*.level' => ['nullable', 'string', 'max:32'],
            'proposed_skills' => ['nullable', 'array', 'max:20'],
            'proposed_skills.*.label' => ['required', 'string', 'max:120'],
            'proposed_skills.*.level' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function offerPayload(): array
    {
        return $this->validated();
    }
}
