<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Permission;

use App\Core\Domain\Identity\Support\ApplicationPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DeleteUserPermissionOverridesRequest extends FormRequest
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
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(ApplicationPermission::allNames())],
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return array_values(array_unique($this->input('permissions', [])));
    }
}
