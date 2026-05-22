<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Requests;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\Document\UserDocumentTypeRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserDocumentRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {
        return 'userDocument';
    }

    protected function prepareForValidation(): void
    {
        $document = $this->route('userDocument');
        if (! $document instanceof UserDocument) {
            return;
        }

        $merge = ['user_id' => $document->user_id];

        if (! $this->filled('document_number') && $document->document_number !== null) {
            $merge['document_number'] = $document->document_number;
        }

        if (! $this->filled('expiry_date') && $document->expiry_date !== null) {
            $merge['expiry_date'] = $document->expiry_date->format('Y-m-d');
        }

        if (! $this->filled('issue_date') && $document->issue_date !== null) {
            $merge['issue_date'] = $document->issue_date->format('Y-m-d');
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => UserDocumentTypeRules::baseFileRules(required: false),
            'type' => UserDocumentTypeRules::typeFieldRules(required: false),
            'document_number' => ['nullable', 'string', 'max:50'],
            'issuing_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_verified_copy' => ['sometimes', 'boolean'],
            'is_sensitive' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $document = $this->route('userDocument');

        UserDocumentTypeRules::applyTypeSpecificRules(
            $validator,
            isUpdate: true,
            ignoreDocumentId: $document instanceof UserDocument ? (int) $document->id : null,
            userId: $document instanceof UserDocument ? (int) $document->user_id : null,
            defaultType: $document instanceof UserDocument ? $this->resolveDocumentType($document) : null,
        );
    }

    private function resolveDocumentType(UserDocument $document): ?DocumentType
    {
        $document->loadMissing('documentType');

        return $document->documentType;
    }
}
