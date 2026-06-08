<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Communication\Requests;

use App\Core\Domain\Communication\Enums\NewsletterScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateNewsletterSubscriptionRequest extends FormRequest
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
            'language' => ['nullable', Rule::in(['fr', 'en'])],
            'scope' => ['nullable', Rule::in(NewsletterScope::values())],
        ];
    }
}
