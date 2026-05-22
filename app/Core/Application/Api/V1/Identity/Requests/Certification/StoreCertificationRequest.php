<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Certification;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\Certification;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCertificationRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return Certification::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'name' => ['required', 'string', 'max:255'],

            'issuing_organization' => ['required', 'string', 'max:255'],

            'document_id' => ['nullable', 'integer', 'exists:user_documents,id'],

            'issue_date' => ['required', 'date'],

            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],

            'credential_id' => ['nullable', 'string', 'max:255'],

            'credential_url' => ['nullable', 'url', 'max:500'],

        ];

    }

}
