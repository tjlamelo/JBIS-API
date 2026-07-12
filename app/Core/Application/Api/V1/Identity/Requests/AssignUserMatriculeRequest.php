<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use App\Core\Domain\Identity\Enums\MatriculeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignUserMatriculeRequest extends FormRequest
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
            'service' => ['required', 'string', Rule::in(MatriculeService::values())],
            'custom_tag' => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9_-]*$/'],
            'force' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service.in' => __('Service de matricule inconnu.'),
            'custom_tag.regex' => __('Le tag ne doit contenir que des lettres, chiffres, _ ou -.'),
        ];
    }
}
