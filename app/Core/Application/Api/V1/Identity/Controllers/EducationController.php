<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\ApproveIdentityRecordRequest;
use App\Core\Application\Api\V1\Identity\Requests\Education\StoreEducationRequest;
use App\Core\Application\Api\V1\Identity\Requests\Education\UpdateEducationRequest;
use App\Core\Application\Api\V1\Identity\Resources\EducationResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Education\ApproveEducationAction;
use App\Core\Domain\Identity\Actions\Education\DeleteEducationAction;
use App\Core\Domain\Identity\Actions\Education\StoreEducationAction;
use App\Core\Domain\Identity\Actions\Education\UpdateEducationAction;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EducationController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreEducationAction $storeEducation,

        private readonly UpdateEducationAction $updateEducation,

        private readonly DeleteEducationAction $deleteEducation,

        private readonly ApproveEducationAction $approveEducation,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', Education::class);

        $query = Education::query()->with(['level', 'country', 'document']);

        $this->scopeIndexToUser($request, $query, 'education');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => EducationResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreEducationRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $education = $this->storeEducation->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Formation enregistrée.'),

            'education' => new EducationResource($education->load(['level', 'country', 'document'])),

        ])->toJsonResponse();

    }

    public function show(Request $request, Education $education): JsonResponse
    {

        $this->authorize('view', $education);

        return BaseResponse::ok([

            'education' => new EducationResource($education->load(['level', 'country', 'document'])),

        ])->toJsonResponse();

    }

    public function update(UpdateEducationRequest $request, Education $education): JsonResponse
    {

        $education = $this->updateEducation->execute($education, $request->validated());

        return BaseResponse::ok([

            'message' => __('Formation mise à jour.'),

            'education' => new EducationResource($education),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, Education $education): JsonResponse
    {

        $this->authorize('delete', $education);

        $this->deleteEducation->execute($education);

        return BaseResponse::ok(['message' => __('Formation supprimée.')])->toJsonResponse();

    }

    public function approve(ApproveIdentityRecordRequest $request, Education $education): JsonResponse
    {

        $this->authorize('approve', $education);

        $education = $this->approveEducation->execute(

            $education,

            (int) $request->user()?->id,

            (bool) $request->boolean('is_approved'),

        );

        return BaseResponse::ok([

            'message' => __('Statut de la formation mis à jour.'),

            'education' => new EducationResource($education),

        ])->toJsonResponse();

    }

}
