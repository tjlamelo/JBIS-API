<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Note;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserNoteRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {
        return 'userNote';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'string'],
            'is_private' => ['sometimes', 'boolean'],
        ];
    }
}
