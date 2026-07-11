<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterProfileRequestCriteria;

final class UpdateRecruiterProfileRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('profileRequest');

        return $request instanceof RecruiterProfileRequest
            && ($this->user()?->can('update', $request) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $criteriaRules = collect(RecruiterProfileRequestCriteria::validationRules())
            ->map(function (mixed $rules): array {
                $arr = is_array($rules) ? $rules : [$rules];
                if (! in_array('sometimes', $arr, true)) {
                    array_unshift($arr, 'sometimes');
                }

                return $arr;
            })
            ->all();

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'quantity_needed' => ['sometimes', 'integer', 'min:1', 'max:'.RecruiterProfileRequestCriteria::MAX_QUANTITY],
            'note' => ['nullable', 'string', 'max:2000'],
            ...$criteriaRules,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updatePayload(): array
    {
        $validated = $this->validated();
        $payload = [];

        foreach (['title', 'quantity_needed', 'note'] as $key) {
            if (array_key_exists($key, $validated)) {
                $payload[$key] = $validated[$key];
            }
        }

        $criteriaKeys = array_keys(RecruiterProfileRequestCriteria::validationRules());
        $criteria = [];
        foreach ($criteriaKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $criteria[$key] = $validated[$key];
            }
        }

        if ($criteria !== []) {
            $payload['criteria'] = $criteria;
        }

        return $payload;
    }
}
