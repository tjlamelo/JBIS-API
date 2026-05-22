<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Requests;

use App\Core\Domain\Identity\Support\Document\UserDocumentTypeRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $auth = $this->user();
        if ($auth === null) {
            return false;
        }

        $targetUserId = $this->filled('user_id')
            ? (int) $this->integer('user_id')
            : (int) $auth->id;

        if ($targetUserId === (int) $auth->id) {
            return true;
        }

        return $auth->can('userdocument.create');
    }

    /**
     * Utilisateur propriétaire du document (candidat = soi-même, admin = id cible).
     */
    public function targetUserId(): int
    {
        $auth = $this->user();

        return $this->filled('user_id')
            ? (int) $this->integer('user_id')
            : (int) $auth?->id;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->targetUserId(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => UserDocumentTypeRules::baseFileRules(required: true),
            'type' => UserDocumentTypeRules::typeFieldRules(required: true),
            'document_number' => ['nullable', 'string', 'max:50'],
            'issuing_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_verified_copy' => ['sometimes', 'boolean'],
            'is_sensitive' => ['sometimes', 'boolean'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        UserDocumentTypeRules::applyTypeSpecificRules($validator, isUpdate: false);
    }
}
