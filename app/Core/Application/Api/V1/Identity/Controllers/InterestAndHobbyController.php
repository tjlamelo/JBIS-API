<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Interest\StoreInterestAndHobbyRequest;
use App\Core\Application\Api\V1\Identity\Requests\Interest\UpdateInterestAndHobbyRequest;
use App\Core\Application\Api\V1\Identity\Resources\InterestAndHobbyResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\InterestHobbie\DeleteInterestAndHobbyAction;
use App\Core\Domain\Identity\Actions\InterestHobbie\StoreInterestAndHobbyAction;
use App\Core\Domain\Identity\Actions\InterestHobbie\UpdateInterestAndHobbyAction;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InterestAndHobbyController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreInterestAndHobbyAction $storeInterestAndHobby,

        private readonly UpdateInterestAndHobbyAction $updateInterestAndHobby,

        private readonly DeleteInterestAndHobbyAction $deleteInterestAndHobby,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', InterestAndHobby::class);

        $query = InterestAndHobby::query();

        $this->scopeIndexToUser($request, $query, 'interestandhobby');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => InterestAndHobbyResource::collection($items->items())->resolve($request),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreInterestAndHobbyRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $item = $this->storeInterestAndHobby->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Centre d\'intérêt enregistré.'),

            'interest' => new InterestAndHobbyResource($item),

        ])->toJsonResponse();

    }

    public function show(Request $request, InterestAndHobby $interestAndHobby): JsonResponse
    {

        $this->authorize('view', $interestAndHobby);

        return BaseResponse::ok([

            'interest' => new InterestAndHobbyResource($interestAndHobby),

        ])->toJsonResponse();

    }

    public function update(UpdateInterestAndHobbyRequest $request, InterestAndHobby $interestAndHobby): JsonResponse
    {

        $interestAndHobby = $this->updateInterestAndHobby->execute($interestAndHobby, $request->validated());

        return BaseResponse::ok([

            'message' => __('Centre d\'intérêt mis à jour.'),

            'interest' => new InterestAndHobbyResource($interestAndHobby),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, InterestAndHobby $interestAndHobby): JsonResponse
    {

        $this->authorize('delete', $interestAndHobby);

        $this->deleteInterestAndHobby->execute($interestAndHobby);

        return BaseResponse::ok(['message' => __('Centre d\'intérêt supprimé.')])->toJsonResponse();

    }
}
