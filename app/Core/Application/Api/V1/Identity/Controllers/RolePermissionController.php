<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Permission\ModifyRolePermissionsRequest;
use App\Core\Application\Api\V1\Identity\Requests\Permission\SyncRolePermissionsRequest;
use App\Core\Application\Api\V1\Identity\Resources\RolePermissionResource;
use App\Core\Domain\Identity\Actions\Permission\GrantRolePermissionsAction;
use App\Core\Domain\Identity\Actions\Permission\RevokeRolePermissionsAction;
use App\Core\Domain\Identity\Actions\Permission\SyncRolePermissionsAction;
use App\Core\Domain\Identity\Support\PermissionManagement;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class RolePermissionController extends Controller
{
    public function __construct(

        private readonly SyncRolePermissionsAction $syncRolePermissions,

        private readonly GrantRolePermissionsAction $grantRolePermissions,

        private readonly RevokeRolePermissionsAction $revokeRolePermissions,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', PermissionManagement::class);

        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        return BaseResponse::ok([

            'roles' => RolePermissionResource::collection($roles),

        ])->toJsonResponse();

    }

    public function show(Request $request, Role $role): JsonResponse
    {

        $this->authorize('viewAny', PermissionManagement::class);

        return BaseResponse::ok([

            'role' => new RolePermissionResource($role->load('permissions')),

        ])->toJsonResponse();

    }

    public function sync(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {

        $this->authorize('update', PermissionManagement::class);

        $role = $this->syncRolePermissions->execute($role, $request->permissionNames());

        return BaseResponse::ok([

            'message' => __('Permissions du rôle mises à jour.'),

            'role' => new RolePermissionResource($role),

        ])->toJsonResponse();

    }

    public function grant(ModifyRolePermissionsRequest $request, Role $role): JsonResponse
    {

        $this->authorize('update', PermissionManagement::class);

        $role = $this->grantRolePermissions->execute($role, $request->permissionNames());

        return BaseResponse::ok([

            'message' => __('Permissions ajoutées au rôle.'),

            'role' => new RolePermissionResource($role),

        ])->toJsonResponse();

    }

    public function revoke(ModifyRolePermissionsRequest $request, Role $role): JsonResponse
    {

        $this->authorize('update', PermissionManagement::class);

        $role = $this->revokeRolePermissions->execute($role, $request->permissionNames());

        return BaseResponse::ok([

            'message' => __('Permissions retirées du rôle.'),

            'role' => new RolePermissionResource($role),

        ])->toJsonResponse();

    }

}
