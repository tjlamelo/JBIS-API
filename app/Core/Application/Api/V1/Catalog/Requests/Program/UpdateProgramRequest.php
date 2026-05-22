<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Program;

use App\Core\Domain\Catalog\Models\Program;

class UpdateProgramRequest extends StoreProgramRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program instanceof Program && ($this->user()?->can('update', $program) ?? false);
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
