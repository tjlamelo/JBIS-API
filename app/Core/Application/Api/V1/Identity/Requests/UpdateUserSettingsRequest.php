<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserSettingsRequest extends FormRequest
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
            'language' => ['sometimes', 'string', Rule::in(['fr', 'en'])],
            'theme' => ['sometimes', 'string', Rule::in(['light', 'dark', 'system'])],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'notifications' => ['sometimes', 'array'],
            'notifications.email' => ['sometimes', 'array'],
            'notifications.push' => ['sometimes', 'array'],
            'notifications.sms' => ['sometimes', 'boolean'],
            'privacy' => ['sometimes', 'array'],
            'marketing' => ['sometimes', 'array'],
            'marketing.newsletter' => ['sometimes', 'boolean'],
            'marketing.newsletter_scope' => ['sometimes', Rule::in(['national', 'international', 'both'])],
            'marketing.partner_offers' => ['sometimes', 'boolean'],
        ];
    }
}
