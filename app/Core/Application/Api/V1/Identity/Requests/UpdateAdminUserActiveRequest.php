<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAdminUserActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }
}
