<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Visa;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserVisaHistoryRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {
        return 'userVisaHistory';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id' => ['sometimes', 'integer', 'exists:countries,id'],
            'visa_type' => ['sometimes', 'string', 'max:100'],
            'visa_number' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'string', Rule::in(['GRANTED', 'REFUSED', 'EXPIRED', 'CANCELLED'])],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'rejection_reason' => ['nullable', 'string'],
            'rejection_date' => ['nullable', 'date'],
            'document_id' => ['nullable', 'integer', 'exists:user_documents,id'],
        ];
    }
}
