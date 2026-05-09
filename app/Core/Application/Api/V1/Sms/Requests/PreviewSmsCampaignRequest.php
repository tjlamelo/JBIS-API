<?php

namespace App\Core\Application\Api\V1\Sms\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewSmsCampaignRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:1000'],
            'sender_id' => ['nullable', 'string', 'max:32'],
            'targeting' => ['required', 'array'],
            'targeting.mode' => ['nullable', 'in:all,users,filters'],
            'targeting.user_ids' => ['nullable', 'array'],
            'targeting.user_ids.*' => ['integer', 'exists:users,id'],
            'targeting.roles' => ['nullable', 'array'],
            'targeting.roles.*' => ['string'],
            'targeting.agency_ids' => ['nullable', 'array'],
            'targeting.agency_ids.*' => ['integer', 'exists:agencies,id'],
            'targeting.manual_numbers' => ['nullable', 'array'],
            'targeting.manual_numbers.*' => ['string', 'max:30'],
        ];
    }
}
