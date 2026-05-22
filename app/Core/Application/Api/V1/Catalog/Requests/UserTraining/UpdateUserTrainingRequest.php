<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\UserTraining;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserTrainingRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {

        return 'userTraining';

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'status' => ['sometimes', 'string', Rule::in(['ONGOING', 'COMPLETED', 'CANCELED'])],

            'started_at' => ['sometimes', 'nullable', 'date'],

            'finished_at' => ['sometimes', 'nullable', 'date'],

        ];

    }

}
