<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Visa;

use App\Core\Application\Api\V1\Identity\Requests\Concerns\AuthorizesStoreViaPolicy;
use App\Core\Domain\Identity\Models\UserPreferredCountry;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserPreferredCountryRequest extends FormRequest
{
    use AuthorizesStoreViaPolicy;

    protected function policyModel(): string
    {
        return UserPreferredCountry::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'priority' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
