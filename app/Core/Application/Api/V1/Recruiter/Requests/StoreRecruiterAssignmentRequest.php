<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRecruiterAssignmentRequest extends FormRequest
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
            'recruiter_organization_id' => ['required', 'integer', 'exists:recruiter_organizations,id'],
            'candidate_user_id' => ['required', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:2000'],
            'visible_sections' => ['nullable', 'array', 'min:1'],
            'visible_sections.*' => ['string', 'in:'.implode(',', \App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection::values())],
        ];
    }
}
