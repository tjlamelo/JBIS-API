<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Document\Requests\DownloadUserDocumentsRequest;
use App\Core\Application\Api\V1\Document\Requests\StoreUserDocumentRequest;
use App\Core\Application\Api\V1\Document\Requests\UpdateUserDocumentRequest;
use App\Core\Application\Api\V1\Document\Requests\ValidateUserDocumentRequest;
use App\Core\Application\Api\V1\Document\Resources\UserDocumentResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Document\DeleteUserDocumentAction;
use App\Core\Domain\Identity\Actions\Document\DownloadUserDocumentAction;
use App\Core\Domain\Identity\Actions\Document\DownloadUserDocumentsZipAction;
use App\Core\Domain\Identity\Actions\Document\StoreUserDocumentAction;
use App\Core\Domain\Identity\Actions\Document\UpdateUserDocumentAction;
use App\Core\Domain\Identity\Actions\Document\ValidateUserDocumentAction;
use App\Core\Domain\Identity\DTOs\Document\UserDocumentDto;
use App\Core\Domain\Identity\Exceptions\Document\DocumentStorageException;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use App\Core\Infrastructure\Cache\AppCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class UserDocumentController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(
        private readonly StoreUserDocumentAction $storeUserDocument,
        private readonly UpdateUserDocumentAction $updateUserDocument,
        private readonly DeleteUserDocumentAction $deleteUserDocument,
        private readonly ValidateUserDocumentAction $validateUserDocument,
        private readonly DownloadUserDocumentAction $downloadUserDocument,
        private readonly DownloadUserDocumentsZipAction $downloadUserDocumentsZip,
        private readonly AppCache $cache,
    ) {}

    public function types(Request $request): JsonResponse
    {
        $includeAll = $request->boolean('all') && $request->user()?->can('viewAny', UserDocument::class);
        $scope = $includeAll ? 'all' : 'candidate';

        $types = $this->cache->remember(
            $this->cache->referenceKey('document_types', app()->getLocale(), $scope),
            3600,
            fn () => DocumentType::catalogForApi(candidatesOnly: ! $includeAll),
        );

        return BaseResponse::ok([
            'types' => $types,
        ])->toJsonResponse();
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserDocument::class);

        $query = UserDocument::query()->with(['issuingCountry']);
        $this->scopeIndexToUser($request, $query, 'userdocument');

        if ($request->filled('type')) {
            $typeCode = strtoupper(trim((string) $request->input('type')));
            $query->whereHas('documentType', fn ($q) => $q->where('code', $typeCode));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $documents = $query->latest()->with(['documentType', 'user.profile'])->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => UserDocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreUserDocumentRequest $request): JsonResponse
    {
        $authUser = $request->user();
        $targetUserId = $request->targetUserId();

        $dto = UserDocumentDto::fromArray($request->validated(), $targetUserId, (int) $authUser?->id);
        $document = $this->storeUserDocument->execute($dto, $request->file('file'));

        return BaseResponse::created([
            'message' => __('Document enregistré avec succès.'),
            'document' => new UserDocumentResource($document->load(['issuingCountry', 'documentType'])),
        ])->toJsonResponse();
    }

    public function show(Request $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('view', $userDocument);

        return BaseResponse::ok([
            'document' => new UserDocumentResource($userDocument->load(['issuingCountry', 'user', 'documentType'])),
        ])->toJsonResponse();
    }

    public function update(UpdateUserDocumentRequest $request, UserDocument $userDocument): JsonResponse
    {
        $document = $this->updateUserDocument->execute(
            $userDocument,
            $request->validated(),
            $request->file('file'),
        );

        return BaseResponse::ok([
            'message' => __('Document mis à jour.'),
            'document' => new UserDocumentResource($document),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('delete', $userDocument);

        $this->deleteUserDocument->execute($userDocument);

        return BaseResponse::ok([
            'message' => __('Document supprimé.'),
        ])->toJsonResponse();
    }

    public function download(Request $request, UserDocument $userDocument): StreamedResponse|JsonResponse
    {
        $this->authorize('download', $userDocument);

        try {
            return $this->downloadUserDocument->execute($userDocument);
        } catch (DocumentStorageException $e) {
            return $this->downloadErrorResponse($e);
        }
    }

    public function downloadBulk(DownloadUserDocumentsRequest $request): BinaryFileResponse|JsonResponse
    {
        $documents = $this->resolveDocumentsForBulkDownload($request);

        foreach ($documents as $document) {
            $this->authorize('download', $document);
        }

        if ($documents->count() === 1) {
            try {
                return $this->downloadUserDocument->execute($documents->first());
            } catch (DocumentStorageException $e) {
                return $this->downloadErrorResponse($e);
            }
        }

        try {
            return $this->downloadUserDocumentsZip->execute($documents);
        } catch (DocumentStorageException $e) {
            return $this->downloadErrorResponse($e);
        }
    }

    public function validateDocument(ValidateUserDocumentRequest $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('validate', $userDocument);

        $status = UserDocumentStatus::from((string) $request->input('status'));

        $document = $this->validateUserDocument->execute(
            $userDocument,
            $status,
            (int) $request->user()?->id,
            $request->input('rejection_reason'),
        );

        return BaseResponse::ok([
            'message' => __('Statut du document mis à jour.'),
            'document' => new UserDocumentResource($document),
        ])->toJsonResponse();
    }

    /**
     * @return Collection<int, UserDocument>
     */
    private function resolveDocumentsForBulkDownload(DownloadUserDocumentsRequest $request)
    {
        if ($request->targetUserId() !== null) {
            $userId = $request->targetUserId();

            return UserDocument::query()
                ->where('user_id', $userId)
                ->latest()
                ->get();
        }

        $documents = UserDocument::query()
            ->whereIn('id', $request->documentIds())
            ->get();

        if ($documents->count() !== count($request->documentIds())) {
            abort(404, __('Un ou plusieurs documents sont introuvables.'));
        }

        return $documents;
    }

    private function downloadErrorResponse(DocumentStorageException $e): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_NOT_FOUND,
            'message' => $e->getMessage(),
            'data' => null,
        ], Response::HTTP_NOT_FOUND);
    }
}
