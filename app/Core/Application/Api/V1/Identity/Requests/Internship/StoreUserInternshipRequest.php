<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Internship;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\UserInternship;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserInternshipRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return UserInternship::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'type' => ['sometimes', 'string', Rule::in(['ACADEMIC', 'PROFESSIONAL', 'OTHER'])],

            'title' => ['required', 'string', 'max:255'],

            'organization' => ['required', 'string', 'max:255'],

            'location' => ['nullable', 'string', 'max:255'],

            'supervisor_name' => ['nullable', 'string', 'max:255'],

            'supervisor_contact' => ['nullable', 'string', 'max:255'],

            'start_date' => ['required', 'date'],

            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],

            'is_current' => ['sometimes', 'boolean'],

            'description' => ['nullable', 'string'],

            'technologies' => ['nullable', 'string'],

            'certificate_document_id' => ['nullable', 'integer', 'exists:user_documents,id'],

            'status' => ['sometimes', 'string', Rule::in(['ONGOING', 'COMPLETED', 'CANCELED'])],

        ];

    }

}
