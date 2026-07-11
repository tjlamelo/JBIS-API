<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Recruiter\Support\RecruiterProfileRequestCriteria;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRecruiterProfileRequestRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'quantity_needed' => ['nullable', 'integer', 'min:1', 'max:'.RecruiterProfileRequestCriteria::MAX_QUANTITY],
            'note' => ['nullable', 'string', 'max:2000'],
            ...RecruiterProfileRequestCriteria::validationRules(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function criteriaPayload(): array
    {
        $validated = $this->validated();

        return array_filter([
            'trade_ids' => $validated['trade_ids'] ?? null,
            'min_years_experience' => $validated['min_years_experience'] ?? null,
            'max_years_experience' => $validated['max_years_experience'] ?? null,
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'preferred_country_ids' => $validated['preferred_country_ids'] ?? null,
            'language_id' => $validated['language_id'] ?? null,
            'language_level_id' => $validated['language_level_id'] ?? null,
        ], static fn ($v) => $v !== null && $v !== []);
    }
}
