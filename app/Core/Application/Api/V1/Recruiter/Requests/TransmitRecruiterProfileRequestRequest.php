<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Recruiter\Enums\RecruiterMaskedField;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransmitRecruiterProfileRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('profileRequest');

        return $request instanceof RecruiterProfileRequest
            && ($this->user()?->can('transmit', $request) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sections = array_map(static fn ($s) => $s->value, RecruiterSharedProfileSection::cases());
        $masked = array_map(static fn ($f) => $f->value, RecruiterMaskedField::cases());

        return [
            'candidate_user_ids' => ['nullable', 'array'],
            'candidate_user_ids.*' => ['integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:2000'],
            'visible_sections' => ['nullable', 'array'],
            'visible_sections.*' => ['string', Rule::in($sections)],
            'masked_fields' => ['nullable', 'array'],
            'masked_fields.*' => ['string', Rule::in($masked)],
        ];
    }
}
