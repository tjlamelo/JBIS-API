<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Internship\StoreUserInternshipRequest;
use App\Core\Application\Api\V1\Identity\Requests\Internship\UpdateUserInternshipRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserInternshipResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Intership\DeleteUserInternshipAction;
use App\Core\Domain\Identity\Actions\Intership\StoreUserInternshipAction;
use App\Core\Domain\Identity\Actions\Intership\UpdateUserInternshipAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserInternshipController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreUserInternshipAction $storeUserInternship,

        private readonly UpdateUserInternshipAction $updateUserInternship,

        private readonly DeleteUserInternshipAction $deleteUserInternship,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', UserInternship::class);

        $query = UserInternship::query()->with(['certificateDocument']);

        $this->scopeIndexToUser($request, $query, 'userinternship');

        if ($request->filled('status')) {

            $query->where('status', (string) $request->input('status'));

        }

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => UserInternshipResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreUserInternshipRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $internship = $this->storeUserInternship->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Stage enregistré.'),

            'internship' => new UserInternshipResource($internship->load(['certificateDocument'])),

        ])->toJsonResponse();

    }

    public function show(Request $request, UserInternship $userInternship): JsonResponse
    {

        $this->authorize('view', $userInternship);

        return BaseResponse::ok([

            'internship' => new UserInternshipResource($userInternship->load(['certificateDocument'])),

        ])->toJsonResponse();

    }

    public function update(UpdateUserInternshipRequest $request, UserInternship $userInternship): JsonResponse
    {

        $userInternship = $this->updateUserInternship->execute($userInternship, $request->validated());

        return BaseResponse::ok([

            'message' => __('Stage mis à jour.'),

            'internship' => new UserInternshipResource($userInternship),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, UserInternship $userInternship): JsonResponse
    {

        $this->authorize('delete', $userInternship);

        $this->deleteUserInternship->execute($userInternship);

        return BaseResponse::ok(['message' => __('Stage supprimé.')])->toJsonResponse();

    }

}
