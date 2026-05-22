<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Note\StoreUserNoteRequest;
use App\Core\Application\Api\V1\Identity\Requests\Note\UpdateUserNoteRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserNoteResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserNote;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserNoteController extends Controller
{
    use ScopesUserOwnedIndex;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserNote::class);

        $query = UserNote::query()->with(['author:id,first_name,last_name,email']);
        $this->scopeIndexToUser($request, $query, 'usernote');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => UserNoteResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreUserNoteRequest $request): JsonResponse
    {
        $targetUser = User::query()->findOrFail($request->targetUserId());

        $note = UserNote::query()->create([
            ...$request->safe()->except(['user_id']),
            'user_id' => $targetUser->id,
            'author_id' => (int) $request->user()->id,
        ]);

        return BaseResponse::created([
            'message' => __('Note enregistrée.'),
            'note' => new UserNoteResource($note->load(['author:id,first_name,last_name,email'])),
        ])->toJsonResponse();
    }

    public function update(UpdateUserNoteRequest $request, UserNote $userNote): JsonResponse
    {
        $userNote->update($request->validated());

        return BaseResponse::ok([
            'message' => __('Note mise à jour.'),
            'note' => new UserNoteResource($userNote->fresh(['author:id,first_name,last_name,email'])),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, UserNote $userNote): JsonResponse
    {
        $this->authorize('delete', $userNote);
        $userNote->delete();

        return BaseResponse::ok(['message' => __('Note supprimée.')])->toJsonResponse();
    }
}
