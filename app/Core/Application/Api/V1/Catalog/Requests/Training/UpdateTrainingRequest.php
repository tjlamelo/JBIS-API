<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Training;

use App\Core\Domain\Catalog\Models\Training;

final class UpdateTrainingRequest extends StoreTrainingRequest
{
    public function authorize(): bool
    {
        $training = $this->route('training');

        return $training instanceof Training && ($this->user()?->can('update', $training) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(function (mixed $rules): array {
                $arr = is_array($rules) ? $rules : [$rules];
                if (! in_array('sometimes', $arr, true)) {
                    array_unshift($arr, 'sometimes');
                }

                return $arr;
            })
            ->all();
    }
}
