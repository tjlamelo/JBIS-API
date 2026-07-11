<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Identity\Queries\AdminUserIdsFromFiltersQuery;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class BulkStoreRecruiterAssignmentRequest extends FormRequest
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
            'note' => ['nullable', 'string', 'max:2000'],
            'visible_sections' => ['nullable', 'array', 'min:1'],
            'visible_sections.*' => ['string', 'in:'.implode(',', RecruiterSharedProfileSection::values())],
            'masked_fields' => ['nullable', 'array'],
            'masked_fields.*' => ['string', 'in:'.implode(',', \App\Core\Domain\Recruiter\Enums\RecruiterMaskedField::values())],
            'candidate_user_ids' => ['nullable', 'array', 'max:'.AdminUserIdsFromFiltersQuery::MAX_BULK_IDS],
            'candidate_user_ids.*' => ['integer', 'exists:users,id'],
            'filters' => ['nullable', 'array'],
            'only_approved' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $ids = $this->input('candidate_user_ids');
            $filters = $this->input('filters');

            $hasIds = is_array($ids) && count($ids) > 0;
            $hasFilters = is_array($filters) && $filters !== [];

            if (! $hasIds && ! $hasFilters) {
                $validator->errors()->add(
                    'candidate_user_ids',
                    __('Indiquez une sélection de candidats ou des filtres de recherche.'),
                );
            }
        });
    }
}
