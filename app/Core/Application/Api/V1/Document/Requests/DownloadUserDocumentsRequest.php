<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class DownloadUserDocumentsRequest extends FormRequest
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
            'document_ids' => ['required_without:user_id', 'array', 'min:1', 'max:100'],
            'document_ids.*' => ['integer', 'distinct', 'exists:user_documents,id'],
            'user_id' => ['required_without:document_ids', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $auth = $this->user();
            $targetUserId = $this->filled('user_id')
                ? (int) $this->integer('user_id')
                : null;

            if ($targetUserId !== null && $targetUserId !== (int) $auth?->id && ! $auth?->can('userdocument.view')) {
                $validator->errors()->add('user_id', __('Accès refusé à ce document.'));
            }
        });
    }

    /**
     * @return list<int>
     */
    public function documentIds(): array
    {
        if (! $this->filled('document_ids')) {
            return [];
        }

        return array_map('intval', $this->input('document_ids', []));
    }

    public function targetUserId(): ?int
    {
        return $this->filled('user_id') ? (int) $this->integer('user_id') : null;
    }
}
