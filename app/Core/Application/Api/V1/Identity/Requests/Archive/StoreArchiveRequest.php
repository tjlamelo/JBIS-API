<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Archive;

use App\Core\Domain\Identity\Enums\ArchiveCategory;
use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Archive::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('category')) {
            $this->merge([
                'category' => strtoupper(trim((string) $this->input('category'))),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
            ],
            'category' => ['nullable', 'string', Rule::in(ArchiveCategory::values())],
            'description' => ['nullable', 'string', 'max:2000'],
            'related_user_id' => ['nullable', 'integer', 'exists:users,id'],
            // Legacy dossier payload
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    public function relatedUserId(): ?int
    {
        if ($this->filled('related_user_id')) {
            return (int) $this->integer('related_user_id');
        }

        if ($this->filled('user_id')) {
            return (int) $this->integer('user_id');
        }

        return null;
    }
}
