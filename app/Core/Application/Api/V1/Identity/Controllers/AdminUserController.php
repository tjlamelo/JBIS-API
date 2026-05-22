<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\StoreAdminUserRequest;
use App\Core\Application\Api\V1\Identity\Requests\UpdateAdminUserActiveRequest;
use App\Core\Application\Api\V1\Identity\Requests\UpdateAdminUserProfileApprovalRequest;
use App\Core\Application\Api\V1\Identity\Requests\UpdateAdminUserRequest;
use App\Core\Application\Api\V1\Identity\Resources\AdminUserResource;
use App\Core\Domain\Identity\Actions\Profile\ModerateUserProfileAction;
use App\Core\Domain\Identity\Actions\User\CreateAdminUserAction;
use App\Core\Domain\Identity\Actions\User\SendAdminUserPasswordResetAction;
use App\Core\Domain\Identity\Actions\User\UpdateAdminUserAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Queries\AdminUserIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUserController extends Controller
{
    public function __construct(

        private readonly AdminUserIndexQuery $userIndexQuery,

        private readonly CreateAdminUserAction $createUser,

        private readonly UpdateAdminUserAction $updateUser,

        private readonly SendAdminUserPasswordResetAction $sendPasswordReset,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', User::class);

        $users = $this->userIndexQuery->paginate($request);

        return BaseResponse::ok([

            'users' => AdminUserResource::collection($users),

            'meta' => [

                'current_page' => $users->currentPage(),

                'last_page' => $users->lastPage(),

                'per_page' => $users->perPage(),

                'total' => $users->total(),

            ],

        ])->toJsonResponse();

    }

    public function stats(): JsonResponse
    {

        $this->authorize('viewAny', User::class);

        return BaseResponse::ok([

            'stats' => [

                'total' => User::query()->count(),

                'active' => User::query()->where('active', true)->count(),

                'inactive' => User::query()->where('active', false)->count(),

                'verified' => User::query()->whereNotNull('email_verified_at')->count(),

            ],

        ])->toJsonResponse();

    }

    public function show(User $user): JsonResponse
    {

        $this->authorize('view', $user);

        $user->load(['roles:id,name', 'profile.approver:id,name', 'sectors:id,name,slug']);

        return BaseResponse::ok([

            'user' => new AdminUserResource($user),

        ])->toJsonResponse();

    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {

        $this->authorize('create', User::class);

        $user = $this->createUser->execute(AdminUserWriteDto::fromArray($request->validated()));

        return BaseResponse::created([

            'message' => __('Utilisateur cree avec succes.'),

            'user' => new AdminUserResource($user),

        ])->toJsonResponse();

    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {

        $this->authorize('update', $user);

        $user = $this->updateUser->execute($user, AdminUserWriteDto::fromArray($request->validated()));

        return BaseResponse::ok([

            'message' => __('Utilisateur mis a jour avec succes.'),

            'user' => new AdminUserResource($user),

        ])->toJsonResponse();

    }

    public function updateActive(UpdateAdminUserActiveRequest $request, User $user): JsonResponse
    {

        $this->authorize('update', $user);

        $user->active = $request->boolean('active');

        $user->save();

        $user->load(['roles:id,name', 'profile.approver:id,name', 'sectors:id,name,slug']);

        return BaseResponse::ok([

            'message' => $user->active

                ? __('Utilisateur active avec succes.')

                : __('Utilisateur desactive avec succes.'),

            'user' => new AdminUserResource($user),

        ])->toJsonResponse();

    }

    public function sendPasswordReset(User $user): JsonResponse
    {

        $this->authorize('update', $user);

        $status = $this->sendPasswordReset->execute($user);

        return BaseResponse::ok([

            'message' => $status,

        ])->toJsonResponse();

    }

    public function updateProfileApproval(

        UpdateAdminUserProfileApprovalRequest $request,

        User $user,

        ModerateUserProfileAction $moderateProfile,

    ): JsonResponse {

        $this->authorize('moderateProfile', $user);

        try {

            $moderateProfile->execute(

                $user,

                $request->boolean('is_approved'),

                (int) $request->user()?->id,

            );

        } catch (\InvalidArgumentException $exception) {

            return BaseResponse::unprocessableEntity([

                'message' => $exception->getMessage(),

            ])->toJsonResponse();

        }

        $user->load(['roles:id,name', 'profile.approver:id,name', 'sectors:id,name,slug']);

        return BaseResponse::ok([

            'message' => $request->boolean('is_approved')

                ? __('Profil approuve.')

                : __('Approbation du profil retiree.'),

            'user' => new AdminUserResource($user),

        ])->toJsonResponse();

    }

}
