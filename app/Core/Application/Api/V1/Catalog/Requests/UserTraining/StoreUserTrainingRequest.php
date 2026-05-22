<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\UserTraining;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\UserTraining;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserTrainingRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {

        return UserTraining::class;

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [

            'user_id' => ['sometimes', 'integer', 'exists:users,id'],

            'training_id' => ['required', 'integer', 'exists:trainings,id'],

            'status' => ['sometimes', 'string', Rule::in(['ONGOING', 'COMPLETED', 'CANCELED'])],

            'started_at' => ['nullable', 'date'],

            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],

        ];

    }

}
