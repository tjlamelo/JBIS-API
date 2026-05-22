<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Visa;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesUpdateViaPolicy;
use App\Core\Domain\Identity\Models\UserPreferredCountry;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserPreferredCountryRequest extends FormRequest
{
    use AuthorizesUpdateViaPolicy;

    protected function routeParameter(): string
    {
        return 'userPreferredCountry';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id' => ['sometimes', 'integer', 'exists:countries,id'],
            'priority' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
