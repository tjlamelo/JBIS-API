<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class ExtractOfferFromTextRequest extends FormRequest
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
            'raw_text' => ['required', 'string', 'min:20', 'max:50000'],
            'trade_id' => ['required', 'integer', 'exists:trades,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'currency' => ['nullable', 'string', 'max:10'],
            'work_mode' => ['nullable', 'string', 'max:50'],
            'scope' => ['nullable', 'string', 'in:full,editorial'],
        ];
    }

    public function scope(): string
    {
        $scope = (string) $this->input('scope', 'full');

        return in_array($scope, ['full', 'editorial'], true) ? $scope : 'full';
    }

    /**
     * @return array<string, mixed>
     */
    public function formContext(): array
    {
        return array_filter([
            'trade_id' => $this->integer('trade_id') ?: null,
            'country_id' => $this->integer('country_id') ?: null,
            'city_id' => $this->integer('city_id') ?: null,
            'company_id' => $this->integer('company_id') ?: null,
            'currency' => $this->input('currency'),
            'work_mode' => $this->input('work_mode'),
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
