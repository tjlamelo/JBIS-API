<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Visa\StoreUserVisaHistoryRequest;
use App\Core\Application\Api\V1\Identity\Requests\Visa\UpdateUserVisaHistoryRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserVisaHistoryResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserVisaHistoryController extends Controller
{
    use ScopesUserOwnedIndex;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserVisaHistory::class);

        $query = UserVisaHistory::query()->with(['country:id,name']);
        $this->scopeIndexToUser($request, $query, 'uservisahistory');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => UserVisaHistoryResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreUserVisaHistoryRequest $request): JsonResponse
    {
        $targetUser = User::query()->findOrFail($request->targetUserId());

        $item = UserVisaHistory::query()->create([
            ...$request->safe()->except(['user_id']),
            'user_id' => $targetUser->id,
        ]);

        return BaseResponse::created([
            'message' => __('Historique visa enregistré.'),
            'visa_history' => new UserVisaHistoryResource($item->load(['country:id,name'])),
        ])->toJsonResponse();
    }

    public function update(UpdateUserVisaHistoryRequest $request, UserVisaHistory $userVisaHistory): JsonResponse
    {
        $userVisaHistory->update($request->validated());

        return BaseResponse::ok([
            'message' => __('Historique visa mis à jour.'),
            'visa_history' => new UserVisaHistoryResource($userVisaHistory->fresh(['country:id,name'])),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, UserVisaHistory $userVisaHistory): JsonResponse
    {
        $this->authorize('delete', $userVisaHistory);
        $userVisaHistory->delete();

        return BaseResponse::ok(['message' => __('Historique visa supprimé.')])->toJsonResponse();
    }
}
