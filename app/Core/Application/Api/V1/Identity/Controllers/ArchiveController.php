<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Archive\StoreArchiveRequest;
use App\Core\Application\Api\V1\Identity\Requests\Archive\UpdateArchiveRequest;
use App\Core\Application\Api\V1\Identity\Resources\ArchiveResource;
use App\Core\Domain\Identity\Actions\Archive\DeleteArchiveAction;
use App\Core\Domain\Identity\Actions\Archive\DownloadArchiveAction;
use App\Core\Domain\Identity\Actions\Archive\ListArchivesAction;
use App\Core\Domain\Identity\Actions\Archive\StoreArchiveAction;
use App\Core\Domain\Identity\Actions\Archive\UpdateArchiveAction;
use App\Core\Domain\Identity\Enums\ArchiveCategory;
use App\Core\Domain\Identity\Models\Archive;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ArchiveController extends Controller
{
    public function __construct(
        private readonly ListArchivesAction $listArchives,
        private readonly StoreArchiveAction $storeArchive,
        private readonly UpdateArchiveAction $updateArchive,
        private readonly DeleteArchiveAction $deleteArchive,
        private readonly DownloadArchiveAction $downloadArchive,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Archive::class);

        $relatedUserId = $request->filled('related_user_id')
            ? (int) $request->integer('related_user_id')
            : ($request->filled('user_id') ? (int) $request->integer('user_id') : null);

        $items = $this->listArchives->execute([
            'category' => $request->query('category'),
            'q' => $request->query('q'),
            'related_user_id' => $relatedUserId,
            'per_page' => (int) $request->integer('per_page', 20),
        ]);

        return BaseResponse::ok([
            'data' => ArchiveResource::collection($items->items()),
            'categories' => ArchiveCategory::values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreArchiveRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $file = $request->file('file');
        abort_if($file === null, 422);

        $archive = $this->storeArchive->execute(
            uploader: $user,
            file: $file,
            category: $request->validated('category'),
            description: $request->validated('description'),
            relatedUserId: $request->relatedUserId(),
            isPublic: $request->boolean('is_public'),
        );

        $archive->load(['uploader:id,name,email', 'relatedUser:id,name,email']);

        return BaseResponse::created([
            'message' => __('Archive enregistrée.'),
            'archive' => new ArchiveResource($archive),
        ])->toJsonResponse();
    }

    public function update(UpdateArchiveRequest $request, Archive $archive): JsonResponse
    {
        $updated = $this->updateArchive->execute($archive, $request->validated());

        return BaseResponse::ok([
            'message' => __('Archive mise à jour.'),
            'archive' => new ArchiveResource($updated),
        ])->toJsonResponse();
    }

    public function destroy(Archive $archive): JsonResponse
    {
        $this->authorize('delete', $archive);
        $this->deleteArchive->execute($archive);

        return BaseResponse::ok(['message' => __('Archive supprimée.')])->toJsonResponse();
    }

    public function download(Archive $archive): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $archive);

        try {
            return $this->downloadArchive->execute($archive);
        } catch (RuntimeException) {
            return BaseResponse::notFound(__('Fichier introuvable.'))->toJsonResponse();
        }
    }
}
