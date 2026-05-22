<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Certification;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateCertificationRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'certification';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'name' => ['sometimes', 'string', 'max:255'],

            'issuing_organization' => ['sometimes', 'string', 'max:255'],

            'document_id' => ['sometimes', 'nullable', 'integer', 'exists:user_documents,id'],

            'issue_date' => ['sometimes', 'date'],

            'expiry_date' => ['sometimes', 'nullable', 'date'],

            'credential_id' => ['sometimes', 'nullable', 'string', 'max:255'],

            'credential_url' => ['sometimes', 'nullable', 'url', 'max:500'],

        ];

    }

}
