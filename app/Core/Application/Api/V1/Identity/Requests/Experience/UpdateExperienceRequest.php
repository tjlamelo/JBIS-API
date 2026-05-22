<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Experience;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateExperienceRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {
        return 'experience';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contract_type_id' => ['sometimes', 'nullable', 'integer', 'exists:contract_types,id'],
            'document_id' => ['sometimes', 'nullable', 'integer', 'exists:user_documents,id'],
            'job_title' => ['sometimes', 'string', 'max:255'],
            'company_name' => ['sometimes', 'string', 'max:255'],
            'country_id' => ['sometimes', 'nullable', 'integer', 'exists:countries,id'],
            'city_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'responsibilities' => ['sometimes', 'nullable', 'string'],
            'achievements' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
