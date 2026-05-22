<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Requests;

use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ValidateUserDocumentRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in([
                UserDocumentStatus::Approved->value,
                UserDocumentStatus::Rejected->value,
            ])],
            'rejection_reason' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(fn () => $this->input('status') === UserDocumentStatus::Rejected->value),
            ],
        ];
    }
}
