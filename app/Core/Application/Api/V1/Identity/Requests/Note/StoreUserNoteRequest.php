<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Note;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\UserNote;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserNoteRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {
        return UserNote::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'content' => ['required', 'string'],
            'is_private' => ['sometimes', 'boolean'],
        ];
    }
}
