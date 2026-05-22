<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\UserSkill;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\UserSkill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserSkillRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return UserSkill::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'skill_id' => ['required', 'integer', 'exists:skills,id'],

            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:80'],

            'level' => ['sometimes', 'string', Rule::in(['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'EXPERT'])],

        ];

    }

}
