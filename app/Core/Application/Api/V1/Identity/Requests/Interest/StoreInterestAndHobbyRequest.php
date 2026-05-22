<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Interest;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use Illuminate\Foundation\Http\FormRequest;

final class StoreInterestAndHobbyRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return InterestAndHobby::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'title' => ['required', 'string', 'max:255'],

            'category' => ['nullable', 'string', 'max:100'],

        ];

    }

}
