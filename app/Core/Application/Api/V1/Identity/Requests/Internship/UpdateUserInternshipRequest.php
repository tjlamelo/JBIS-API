<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Internship;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserInternshipRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'userInternship';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'type' => ['sometimes', 'string', Rule::in(['ACADEMIC', 'PROFESSIONAL', 'OTHER'])],

            'title' => ['sometimes', 'string', 'max:255'],

            'organization' => ['sometimes', 'string', 'max:255'],

            'location' => ['sometimes', 'nullable', 'string', 'max:255'],

            'supervisor_name' => ['sometimes', 'nullable', 'string', 'max:255'],

            'supervisor_contact' => ['sometimes', 'nullable', 'string', 'max:255'],

            'start_date' => ['sometimes', 'date'],

            'end_date' => ['sometimes', 'nullable', 'date'],

            'is_current' => ['sometimes', 'boolean'],

            'description' => ['sometimes', 'nullable', 'string'],

            'technologies' => ['sometimes', 'nullable', 'string'],

            'certificate_document_id' => ['sometimes', 'nullable', 'integer', 'exists:user_documents,id'],

            'status' => ['sometimes', 'string', Rule::in(['ONGOING', 'COMPLETED', 'CANCELED'])],

        ];

    }

}
