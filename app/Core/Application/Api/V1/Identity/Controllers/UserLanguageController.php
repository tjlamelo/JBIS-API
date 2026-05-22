<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\ApproveIdentityRecordRequest;
use App\Core\Application\Api\V1\Identity\Requests\Language\StoreUserLanguageRequest;
use App\Core\Application\Api\V1\Identity\Requests\Language\UpdateUserLanguageRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserLanguageResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Language\ApproveUserLanguageAction;
use App\Core\Domain\Identity\Actions\Language\DeleteUserLanguageAction;
use App\Core\Domain\Identity\Actions\Language\StoreUserLanguageAction;
use App\Core\Domain\Identity\Actions\Language\UpdateUserLanguageAction;
use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserLanguageController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreUserLanguageAction $storeUserLanguage,

        private readonly UpdateUserLanguageAction $updateUserLanguage,

        private readonly DeleteUserLanguageAction $deleteUserLanguage,

        private readonly ApproveUserLanguageAction $approveUserLanguage,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', Language::class);

        $query = Language::query()->with(['language', 'languageLevel']);

        $this->scopeIndexToUser($request, $query, 'userlanguage');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => UserLanguageResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreUserLanguageRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $userLanguage = $this->storeUserLanguage->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Langue enregistrée.'),

            'user_language' => new UserLanguageResource($userLanguage->load(['language', 'languageLevel'])),

        ])->toJsonResponse();

    }

    public function show(Request $request, Language $userLanguage): JsonResponse
    {

        $this->authorize('view', $userLanguage);

        return BaseResponse::ok([

            'user_language' => new UserLanguageResource($userLanguage->load(['language', 'languageLevel'])),

        ])->toJsonResponse();

    }

    public function update(UpdateUserLanguageRequest $request, Language $userLanguage): JsonResponse
    {

        $userLanguage = $this->updateUserLanguage->execute($userLanguage, $request->validated());

        return BaseResponse::ok([

            'message' => __('Langue mise à jour.'),

            'user_language' => new UserLanguageResource($userLanguage->load(['language', 'languageLevel'])),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, Language $userLanguage): JsonResponse
    {

        $this->authorize('delete', $userLanguage);

        $this->deleteUserLanguage->execute($userLanguage);

        return BaseResponse::ok(['message' => __('Langue supprimée.')])->toJsonResponse();

    }

    public function approve(ApproveIdentityRecordRequest $request, Language $userLanguage): JsonResponse
    {

        $this->authorize('approve', $userLanguage);

        $userLanguage = $this->approveUserLanguage->execute(

            $userLanguage,

            (int) $request->user()?->id,

            (bool) $request->boolean('is_approved'),

        );

        return BaseResponse::ok([

            'message' => __('Statut de la langue mis à jour.'),

            'user_language' => new UserLanguageResource($userLanguage->load(['language', 'languageLevel'])),

        ])->toJsonResponse();

    }

}
