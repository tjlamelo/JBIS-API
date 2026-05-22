<?php

namespace App\Core\Application\Api\V1\Mail\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMailboxRequest extends FormRequest
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
            'local_part' => ['required', 'string', 'max:128', 'regex:/^[a-zA-Z0-9._%+-]+$/'],
            'password' => ['required', 'string', 'min:8', 'max:191'],
            'quota_mb' => ['nullable', 'integer', 'min:0', 'max:20480'],
        ];
    }
}
