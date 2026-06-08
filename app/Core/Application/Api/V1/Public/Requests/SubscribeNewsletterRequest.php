<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Requests;

use App\Core\Domain\Communication\Enums\NewsletterScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SubscribeNewsletterRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', Rule::in(['fr', 'en'])],
            'scope' => ['nullable', Rule::in(NewsletterScope::values())],
            'source' => ['nullable', 'string', 'max:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user() && ! $this->filled('email')) {
            $this->merge(['email' => $this->user()->email]);
        }
    }
}
