<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRecruiterOrganizationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:63', 'regex:/^[a-z0-9-]+$/', Rule::unique('recruiter_organizations', 'slug')],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
