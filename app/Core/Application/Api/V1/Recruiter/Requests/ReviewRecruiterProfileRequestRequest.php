<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewRecruiterProfileRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('profileRequest');

        return $request instanceof RecruiterProfileRequest
            && ($this->user()?->can('review', $request) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', Rule::in(['reject', 'needs_changes'])],
            'staff_note' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
