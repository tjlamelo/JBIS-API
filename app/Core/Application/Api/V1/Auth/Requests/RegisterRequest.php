<?php

namespace App\Core\Application\Api\V1\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (
            $this->filled('password') &&
            ! $this->has('password_confirmation')
        ) {
            $this->merge([
                'password_confirmation' => $this->input('password'),
            ]);
        }

        if ($this->has('newsletter')) {
            $this->merge([
                'newsletter' => filter_var($this->input('newsletter'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

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
            'name' => ['required_without:user_name', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number1' => ['nullable', 'string', 'max:20', 'unique:users,phone_number1'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'newsletter' => ['sometimes', 'boolean'],
            'newsletter_scope' => ['sometimes', 'in:national,international,both'],
        ];
    }
}
