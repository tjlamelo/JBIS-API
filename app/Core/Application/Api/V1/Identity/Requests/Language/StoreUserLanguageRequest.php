<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Language;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserLanguageRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return Language::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'language_id' => ['required', 'integer', 'exists:languages,id'],

            'language_level_id' => ['required', 'integer', 'exists:language_levels,id'],

        ];

    }

}
