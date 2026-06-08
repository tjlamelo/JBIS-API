<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UnsubscribeNewsletterRequest extends FormRequest
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
            'token' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('token') && ! $this->filled('email')) {
                $validator->errors()->add('token', __('Token ou e-mail requis.'));
            }
        });
    }
}
