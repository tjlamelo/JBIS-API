<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Archive;

use App\Core\Domain\Identity\Enums\ArchiveCategory;
use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Archive $archive */
        $archive = $this->route('archive');

        return $this->user()?->can('update', $archive) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'nullable', 'string', Rule::in(ArchiveCategory::values())],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
