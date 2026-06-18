<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'category_id' => ['nullable', 'integer'],
            'contract_type_id' => ['nullable', 'integer'],
            'country_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'salary_min' => ['nullable', 'numeric'],
            'salary_max' => ['nullable', 'numeric'],
            'work_mode' => ['nullable', 'string', 'max:32'],
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
