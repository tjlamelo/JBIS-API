<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveIdentityRecordRequest extends FormRequest
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
            'is_approved' => ['required', 'boolean'],
        ];
    }
}
