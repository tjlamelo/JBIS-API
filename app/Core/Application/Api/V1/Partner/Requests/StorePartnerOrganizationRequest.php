<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePartnerOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:partner_organizations,slug'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
