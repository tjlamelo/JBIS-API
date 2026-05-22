<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Training;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Requests\Training\StoreTrainingRequest;
use App\Core\Application\Api\V1\Catalog\Requests\Training\UpdateTrainingRequest;
use App\Core\Application\Api\V1\Catalog\Resources\Training\TrainingResource;
use App\Core\Domain\Catalog\Actions\Training\CreateTrainingAction;
use App\Core\Domain\Catalog\Actions\Training\DeleteTrainingAction;
use App\Core\Domain\Catalog\Actions\Training\UpdateTrainingAction;
use App\Core\Domain\Catalog\Models\Training;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminTrainingController extends Controller
{
    public function __construct(
        private readonly CreateTrainingAction $createTraining,
        private readonly UpdateTrainingAction $updateTraining,
        private readonly DeleteTrainingAction $deleteTraining,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Training::class);

        $search = trim((string) $request->query('search', ''));
        $active = $request->query('is_active');

        $trainings = Training::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('organization', 'like', "%{$search}%");
                });
            })
            ->when($active === '1' || $active === 'true', fn ($q) => $q->where('is_active', true))
            ->when($active === '0' || $active === 'false', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('updated_at')
            ->paginate(min(100, max(1, (int) $request->query('per_page', 20))));

        return BaseResponse::ok([
            'trainings' => TrainingResource::collection($trainings),
            'meta' => [
                'current_page' => $trainings->currentPage(),
                'last_page' => $trainings->lastPage(),
                'per_page' => $trainings->perPage(),
                'total' => $trainings->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreTrainingRequest $request): JsonResponse
    {
        $training = $this->createTraining->execute($request->toDto());

        return BaseResponse::created([
            'message' => __('Formation catalogue créée.'),
            'training' => new TrainingResource($training),
        ])->toJsonResponse();
    }

    public function show(Training $training): JsonResponse
    {
        $this->authorize('view', $training);

        return BaseResponse::ok([
            'training' => new TrainingResource($training),
        ])->toJsonResponse();
    }

    public function update(UpdateTrainingRequest $request, Training $training): JsonResponse
    {
        $training = $this->updateTraining->execute($training->id, $request->toDto());

        return BaseResponse::ok([
            'message' => __('Formation catalogue mise à jour.'),
            'training' => new TrainingResource($training),
        ])->toJsonResponse();
    }

    public function destroy(Training $training): JsonResponse
    {
        $this->authorize('delete', $training);

        $this->deleteTraining->execute($training->id);

        return BaseResponse::ok([
            'message' => __('Formation catalogue supprimée.'),
        ])->toJsonResponse();
    }
}
