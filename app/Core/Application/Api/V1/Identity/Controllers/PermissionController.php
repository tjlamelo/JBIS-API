<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Identity\Support\PermissionManagement;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

final class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', PermissionManagement::class);

        $permissions = Permission::query()->orderBy('name')->get();

        return BaseResponse::ok([

            'permissions' => $permissions->pluck('name')->values(),

            'grouped' => ApplicationPermission::groupedByResource(),

        ])->toJsonResponse();

    }

}
