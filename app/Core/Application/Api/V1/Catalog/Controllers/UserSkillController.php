<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Requests\UserSkill\StoreUserSkillRequest;
use App\Core\Application\Api\V1\Catalog\Requests\UserSkill\UpdateUserSkillRequest;
use App\Core\Application\Api\V1\Catalog\Resources\UserSkillResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Skill\DeleteUserSkillAction;
use App\Core\Domain\Identity\Actions\Skill\StoreUserSkillAction;
use App\Core\Domain\Identity\Actions\Skill\UpdateUserSkillAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserSkillController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreUserSkillAction $storeUserSkill,

        private readonly UpdateUserSkillAction $updateUserSkill,

        private readonly DeleteUserSkillAction $deleteUserSkill,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', UserSkill::class);

        $query = UserSkill::query()->with(['skill']);

        $this->scopeIndexToUser($request, $query, 'userskill');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => UserSkillResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreUserSkillRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $userSkill = $this->storeUserSkill->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Compétence enregistrée.'),

            'user_skill' => new UserSkillResource($userSkill->load(['skill'])),

        ])->toJsonResponse();

    }

    public function show(Request $request, UserSkill $userSkill): JsonResponse
    {

        $this->authorize('view', $userSkill);

        return BaseResponse::ok([

            'user_skill' => new UserSkillResource($userSkill->load(['skill'])),

        ])->toJsonResponse();

    }

    public function update(UpdateUserSkillRequest $request, UserSkill $userSkill): JsonResponse
    {

        $userSkill = $this->updateUserSkill->execute($userSkill, $request->validated());

        return BaseResponse::ok([

            'message' => __('Compétence mise à jour.'),

            'user_skill' => new UserSkillResource($userSkill),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, UserSkill $userSkill): JsonResponse
    {

        $this->authorize('delete', $userSkill);

        $this->deleteUserSkill->execute($userSkill);

        return BaseResponse::ok(['message' => __('Compétence supprimée.')])->toJsonResponse();

    }

}
