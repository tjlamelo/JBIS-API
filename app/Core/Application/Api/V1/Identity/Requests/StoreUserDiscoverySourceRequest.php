<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUserDiscoverySourceRequest extends FormRequest
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
            'discovery_source_id' => ['required', 'integer', 'exists:discovery_sources,id'],
            'discovery_source_other' => ['nullable', 'string', 'max:255'],
        ];
    }
}
