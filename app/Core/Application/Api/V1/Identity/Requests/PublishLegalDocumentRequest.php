<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use App\Core\Domain\Identity\Support\ConsentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.access') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ConsentType::ALL)],
            'version' => ['required', 'string', 'max:64'],
            'title' => ['required', 'array'],
            'title.fr' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'array'],
            'content.fr' => ['required', 'string'],
            'content.en' => ['nullable', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'requires_reacceptance' => ['sometimes', 'boolean'],
        ];
    }
}
