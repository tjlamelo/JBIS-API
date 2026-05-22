<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'process_flow_id' => ['nullable', 'integer', 'exists:process_flows,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                $this->input('offer_id') === null
                && $this->input('program_id') === null
                && $this->input('process_flow_id') === null
            ) {
                $validator->errors()->add('offer_id', 'Une offre, un programme ou un process_flow_id est requis.');
            }
        });
    }
}
