<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AdminStoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'offer_id' => ['required', 'integer', 'exists:offers,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'process_flow_id' => ['nullable', 'integer', 'exists:process_flows,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // At least offer is required (validated above). Keep hook for future constraints.
            if (! $this->filled('offer_id') && ! $this->filled('program_id') && ! $this->filled('process_flow_id')) {
                $validator->errors()->add('offer_id', __('Une offre est requise pour l\'inscription privée.'));
            }
        });
    }
}
