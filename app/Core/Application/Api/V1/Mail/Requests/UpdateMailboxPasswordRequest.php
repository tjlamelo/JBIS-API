<?php

namespace App\Core\Application\Api\V1\Mail\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailboxPasswordRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:8', 'max:191'],
        ];
    }
}
