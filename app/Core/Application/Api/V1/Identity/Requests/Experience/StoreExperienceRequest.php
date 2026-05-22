<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Experience;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;

final class StoreExperienceRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {
        return Experience::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'contract_type_id' => ['nullable', 'integer', 'exists:contract_types,id'],
            'document_id' => ['nullable', 'integer', 'exists:user_documents,id'],
            'job_title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['sometimes', 'boolean'],
            'responsibilities' => ['nullable', 'string'],
            'achievements' => ['nullable', 'string'],
        ];
    }
}
