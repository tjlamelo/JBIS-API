<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Experience\StoreExperienceRequest;
use App\Core\Application\Api\V1\Identity\Requests\Experience\UpdateExperienceRequest;
use App\Core\Application\Api\V1\Identity\Requests\ValidateIdentityRecordStatusRequest;
use App\Core\Application\Api\V1\Identity\Resources\ExperienceResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Experience\DeleteExperienceAction;
use App\Core\Domain\Identity\Actions\Experience\StoreExperienceAction;
use App\Core\Domain\Identity\Actions\Experience\UpdateExperienceAction;
use App\Core\Domain\Identity\Actions\Experience\ValidateExperienceAction;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExperienceController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(
        private readonly StoreExperienceAction $storeExperience,
        private readonly UpdateExperienceAction $updateExperience,
        private readonly DeleteExperienceAction $deleteExperience,
        private readonly ValidateExperienceAction $validateExperience,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Experience::class);

        $query = Experience::query()->with(['contractType', 'country', 'document']);
        $this->scopeIndexToUser($request, $query, 'experience');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => ExperienceResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreExperienceRequest $request): JsonResponse
    {
        $targetUser = User::query()->findOrFail($request->targetUserId());
        $experience = $this->storeExperience->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([
            'message' => __('Expérience enregistrée.'),
            'experience' => new ExperienceResource($experience->load(['contractType', 'country', 'document'])),
        ])->toJsonResponse();
    }

    public function show(Request $request, Experience $experience): JsonResponse
    {
        $this->authorize('view', $experience);

        return BaseResponse::ok([
            'experience' => new ExperienceResource($experience->load(['contractType', 'country', 'document'])),
        ])->toJsonResponse();
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): JsonResponse
    {
        $experience = $this->updateExperience->execute($experience, $request->validated());

        return BaseResponse::ok([
            'message' => __('Expérience mise à jour.'),
            'experience' => new ExperienceResource($experience),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, Experience $experience): JsonResponse
    {
        $this->authorize('delete', $experience);

        $this->deleteExperience->execute($experience);

        return BaseResponse::ok([
            'message' => __('Expérience supprimée.'),
        ])->toJsonResponse();
    }

    public function validateItem(ValidateIdentityRecordStatusRequest $request, Experience $experience): JsonResponse
    {
        $this->authorize('validate', $experience);

        $experience = $this->validateExperience->execute(
            $experience,
            (string) $request->input('status'),
            (int) $request->user()?->id,
        );

        return BaseResponse::ok([
            'message' => __('Statut de l\'expérience mis à jour.'),
            'experience' => new ExperienceResource($experience),
        ])->toJsonResponse();
    }
}
