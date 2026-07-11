<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewPartnerCohortRequest extends FormRequest
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
            'decision' => ['required', 'string', Rule::in(['approve', 'reject', 'under_review', 'close'])],
            'staff_note' => ['nullable', 'string', 'max:5000'],
            'rejection_reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
