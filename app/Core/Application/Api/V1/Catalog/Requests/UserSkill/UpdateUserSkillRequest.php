<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\UserSkill;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserSkillRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'userSkill';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'years_of_experience' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:80'],

            'level' => ['sometimes', 'string', Rule::in(['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'EXPERT'])],

        ];

    }

}
