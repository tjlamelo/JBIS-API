<?php

namespace App\Core\Application\Api\V1\Mail\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMailCampaignRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'content.title' => ['nullable', 'string', 'max:180'],
            'content.intro' => ['nullable', 'string', 'max:1000'],
            'content.sections' => ['nullable', 'array'],
            'content.sections.*.title' => ['nullable', 'string', 'max:180'],
            'content.sections.*.text' => ['required_with:content.sections', 'string', 'max:5000'],
            'content.ctas' => ['nullable', 'array', 'max:3'],
            'content.ctas.*.label' => ['required_with:content.ctas', 'string', 'max:40'],
            'content.ctas.*.url' => ['required_with:content.ctas', 'url', 'max:500'],
            'content.footer_note' => ['nullable', 'string', 'max:500'],
            'content.variables' => ['nullable', 'array'],
            'send_mode' => ['nullable', 'in:queue,sync'],
            'from_name' => ['nullable', 'string', 'max:120'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'targeting' => ['required', 'array'],
            'targeting.mode' => ['nullable', 'in:all,users,filters'],
            'targeting.user_ids' => ['nullable', 'array'],
            'targeting.user_ids.*' => ['integer', 'exists:users,id'],
            'targeting.roles' => ['nullable', 'array'],
            'targeting.roles.*' => ['string'],
            'targeting.agency_ids' => ['nullable', 'array'],
            'targeting.agency_ids.*' => ['integer', 'exists:agencies,id'],
        ];
    }
}
