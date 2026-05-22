<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Interest;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateInterestAndHobbyRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'interestAndHobby';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'title' => ['sometimes', 'string', 'max:255'],

            'category' => ['sometimes', 'nullable', 'string', 'max:100'],

        ];

    }

}
