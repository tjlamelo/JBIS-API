<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Requests\UserTraining\StoreUserTrainingRequest;
use App\Core\Application\Api\V1\Catalog\Requests\UserTraining\UpdateUserTrainingRequest;
use App\Core\Application\Api\V1\Catalog\Resources\UserTrainingResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Training\DeleteUserTrainingAction;
use App\Core\Domain\Identity\Actions\Training\EnrollUserTrainingAction;
use App\Core\Domain\Identity\Actions\Training\UpdateUserTrainingAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserTraining;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserTrainingController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly EnrollUserTrainingAction $enrollUserTraining,

        private readonly UpdateUserTrainingAction $updateUserTraining,

        private readonly DeleteUserTrainingAction $deleteUserTraining,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', UserTraining::class);

        $query = UserTraining::query()->with(['training:id,title,domain,organization']);

        $this->scopeIndexToUser($request, $query, 'usertraining');

        if ($request->filled('status')) {

            $query->where('status', (string) $request->input('status'));

        }

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => UserTrainingResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreUserTrainingRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $userTraining = $this->enrollUserTraining->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Formation JBIS enregistrée.'),

            'user_training' => new UserTrainingResource($userTraining),

        ])->toJsonResponse();

    }

    public function show(Request $request, UserTraining $userTraining): JsonResponse
    {

        $this->authorize('view', $userTraining);

        return BaseResponse::ok([

            'user_training' => new UserTrainingResource($userTraining),

        ])->toJsonResponse();

    }

    public function update(UpdateUserTrainingRequest $request, UserTraining $userTraining): JsonResponse
    {

        $userTraining = $this->updateUserTraining->execute($userTraining, $request->validated());

        return BaseResponse::ok([

            'message' => __('Formation JBIS mise à jour.'),

            'user_training' => new UserTrainingResource($userTraining),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, UserTraining $userTraining): JsonResponse
    {

        $this->authorize('delete', $userTraining);

        $this->deleteUserTraining->execute($userTraining);

        return BaseResponse::ok(['message' => __('Formation JBIS supprimée.')])->toJsonResponse();

    }

}
