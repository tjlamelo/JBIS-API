<?php

namespace App\Core\Application\Api\V1\Mail\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailboxQuotaRequest extends FormRequest
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
            'quota_mb' => ['required', 'integer', 'min:0', 'max:20480'],
        ];
    }
}
