<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Archive;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;

final class StoreArchiveRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {
        return Archive::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'file' => ['required', 'file', 'max:51200'],
            'category' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
            'file_type' => ['nullable', 'string', 'max:50'],
            'is_public' => ['sometimes', 'boolean'],
            'disk' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
