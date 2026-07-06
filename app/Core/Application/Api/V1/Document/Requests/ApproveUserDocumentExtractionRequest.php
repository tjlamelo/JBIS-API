<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveUserDocumentExtractionRequest extends FormRequest
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
            'draft' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function draftOverrides(): ?array
    {
        $draft = $this->input('draft');

        return is_array($draft) ? $draft : null;
    }
}
