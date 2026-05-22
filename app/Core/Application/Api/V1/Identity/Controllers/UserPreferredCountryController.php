<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Visa\StoreUserPreferredCountryRequest;
use App\Core\Application\Api\V1\Identity\Requests\Visa\UpdateUserPreferredCountryRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserPreferredCountryResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserPreferredCountry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPreferredCountryController extends Controller
{
    use ScopesUserOwnedIndex;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserPreferredCountry::class);

        $query = UserPreferredCountry::query()->with(['country:id,name']);
        $this->scopeIndexToUser($request, $query, 'userpreferredcountry');

        $items = $query->orderBy('priority')->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => UserPreferredCountryResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreUserPreferredCountryRequest $request): JsonResponse
    {
        $targetUser = User::query()->findOrFail($request->targetUserId());

        $item = UserPreferredCountry::query()->create([
            ...$request->safe()->except(['user_id']),
            'user_id' => $targetUser->id,
        ]);

        return BaseResponse::created([
            'message' => __('Pays cible enregistré.'),
            'preferred_country' => new UserPreferredCountryResource($item->load(['country:id,name'])),
        ])->toJsonResponse();
    }

    public function update(UpdateUserPreferredCountryRequest $request, UserPreferredCountry $userPreferredCountry): JsonResponse
    {
        $userPreferredCountry->update($request->validated());

        return BaseResponse::ok([
            'message' => __('Pays cible mis à jour.'),
            'preferred_country' => new UserPreferredCountryResource($userPreferredCountry->fresh(['country:id,name'])),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, UserPreferredCountry $userPreferredCountry): JsonResponse
    {
        $this->authorize('delete', $userPreferredCountry);
        $userPreferredCountry->delete();

        return BaseResponse::ok(['message' => __('Pays cible supprimé.')])->toJsonResponse();
    }
}
