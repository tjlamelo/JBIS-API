<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRecruiterOrganizationRequest extends FormRequest
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
        $organization = $this->route('recruiterOrganization');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'company_id' => ['sometimes', 'nullable', 'integer', 'exists:companies,id'],
            'status' => ['sometimes', Rule::enum(RecruiterOrganizationStatus::class)],
        ];
    }
}
