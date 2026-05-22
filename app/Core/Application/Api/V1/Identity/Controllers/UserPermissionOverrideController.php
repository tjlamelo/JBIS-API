<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Permission\DeleteUserPermissionOverridesRequest;
use App\Core\Application\Api\V1\Identity\Requests\Permission\SetUserPermissionOverridesRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserPermissionOverrideResource;
use App\Core\Domain\Identity\Actions\Permission\DeleteUserPermissionOverridesAction;
use App\Core\Domain\Identity\Actions\Permission\SetUserPermissionOverridesAction;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPermissionOverrideController extends Controller
{
    public function __construct(

        private readonly SetUserPermissionOverridesAction $setOverrides,

        private readonly DeleteUserPermissionOverridesAction $deleteOverrides,

    ) {}

    public function index(Request $request, User $user): JsonResponse
    {

        $this->authorize('managePermissionOverrides', $user);

        $overrides = $user->permissionOverrides()->orderBy('permission_name')->get();

        return BaseResponse::ok([

            'user_id' => $user->id,

            'overrides' => UserPermissionOverrideResource::collection($overrides),

            'effective_permissions' => $user->getAllPermissions()->pluck('name')->values(),

        ])->toJsonResponse();

    }

    public function store(SetUserPermissionOverridesRequest $request, User $user): JsonResponse
    {

        $this->authorize('managePermissionOverrides', $user);

        $saved = $this->setOverrides->execute($user, $request->overrides());

        return BaseResponse::ok([

            'message' => __('Overrides de permissions enregistrés.'),

            'overrides' => UserPermissionOverrideResource::collection($saved),

        ])->toJsonResponse();

    }

    public function destroy(DeleteUserPermissionOverridesRequest $request, User $user): JsonResponse
    {

        $this->authorize('managePermissionOverrides', $user);

        $this->deleteOverrides->execute($user, $request->permissionNames());

        return BaseResponse::ok([

            'message' => __('Overrides de permissions supprimés.'),

        ])->toJsonResponse();

    }

}
