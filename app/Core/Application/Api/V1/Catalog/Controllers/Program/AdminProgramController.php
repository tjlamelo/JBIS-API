<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Program;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Program\ProgramIndexQuery;
use App\Core\Application\Api\V1\Catalog\Requests\Program\StoreProgramRequest;
use App\Core\Application\Api\V1\Catalog\Requests\Program\UpdateProgramRequest;
use App\Core\Application\Api\V1\Catalog\Resources\Program\ProgramResource;
use App\Core\Domain\Catalog\Actions\Program\CreateProgramAction;
use App\Core\Domain\Catalog\Actions\Program\DeleteProgramAction;
use App\Core\Domain\Catalog\Actions\Program\UpdateProgramAction;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Shared\Media\Actions\StoreMediaAction;
use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminProgramController extends Controller
{
    public function __construct(
        private readonly ProgramIndexQuery $programIndexQuery,
        private readonly CreateProgramAction $createProgramAction,
        private readonly UpdateProgramAction $updateProgramAction,
        private readonly DeleteProgramAction $deleteProgramAction,
        private readonly StoreMediaAction $storeMediaAction,
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Program::class);

        $programs = $this->programIndexQuery
            ->with(['geographicZone'])
            ->withCount('offers')
            ->paginate($request->integer('per_page', 15));

        return BaseResponse::ok([
            'programs' => ProgramResource::collection($programs),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
                'total' => $programs->total(),
                'per_page' => $programs->perPage(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreProgramRequest $request): JsonResponse
    {
        $program = $this->createProgramAction->execute($request->toDto());
        $program->load(['geographicZone', 'requiredDocuments', 'languages']);
        $program->loadCount('offers');

        return BaseResponse::created([
            'message' => __('Programme cree avec succes.'),
            'program' => new ProgramResource($program),
        ])->toJsonResponse();
    }

    public function show(Program $program): JsonResponse
    {
        $this->authorize('view', $program);

        $program->load(['geographicZone', 'requiredDocuments', 'languages', 'offers']);
        $program->loadCount('offers');

        return BaseResponse::ok([
            'program' => new ProgramResource($program),
        ])->toJsonResponse();
    }

    public function update(UpdateProgramRequest $request, Program $program): JsonResponse
    {
        $program = $this->updateProgramAction->execute($program->id, $request->toDto());
        $program->load(['geographicZone', 'requiredDocuments', 'languages']);
        $program->loadCount('offers');

        return BaseResponse::ok([
            'message' => __('Programme mis a jour avec succes.'),
            'program' => new ProgramResource($program),
        ])->toJsonResponse();
    }

    public function destroy(Program $program): JsonResponse
    {
        $this->authorize('delete', $program);

        $this->deleteProgramAction->execute($program->id);

        return BaseResponse::ok([
            'message' => __('Programme supprime avec succes.'),
        ])->toJsonResponse();
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('uploadMedia', Program::class);

        $validated = $request->validate([
            'image' => ['required_without:photo', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
            'photo' => ['required_without:image', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $file = $validated['image'] ?? $validated['photo'];

        $uploaded = $this->storeMediaAction->execute(
            $file,
            'catalog/programs/flyers'
        );

        $media = $uploaded->toArray();
        $urls = $this->mediaUrlResolver->all($media);

        return BaseResponse::ok([
            'message' => __('Image telechargee avec succes.'),
            'image' => $urls,
            'media' => $media,
        ])->toJsonResponse();
    }
}
