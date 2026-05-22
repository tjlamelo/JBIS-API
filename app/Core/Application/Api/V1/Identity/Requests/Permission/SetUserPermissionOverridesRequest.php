<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Permission;

use App\Core\Domain\Identity\Support\ApplicationPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SetUserPermissionOverridesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(ApplicationPermission::PERMISSION_MANAGE) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'overrides' => ['required', 'array', 'min:1'],
            'overrides.*.permission_name' => ['required', 'string', Rule::in(ApplicationPermission::allNames())],
            'overrides.*.effect' => ['required', 'string', Rule::in(['allow', 'deny'])],
        ];
    }

    /**
     * @return list<array{permission_name: string, effect: string}>
     */
    public function overrides(): array
    {
        return $this->input('overrides', []);
    }
}
