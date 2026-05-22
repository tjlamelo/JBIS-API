<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Language;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserLanguageRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'userLanguage';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'language_id' => ['sometimes', 'integer', 'exists:languages,id'],

            'language_level_id' => ['sometimes', 'integer', 'exists:language_levels,id'],

        ];

    }

}
