<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Document\Requests\ApproveUserDocumentExtractionRequest;
use App\Core\Application\Api\V1\Document\Resources\UserDocumentExtractionResource;
use App\Core\Domain\Identity\Actions\Document\ApplyUserDocumentExtractionAction;
use App\Core\Domain\Identity\Actions\Document\ReprocessUserDocumentExtractionAction;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserDocumentExtractionController extends Controller
{
    public function __construct(
        private readonly ApplyUserDocumentExtractionAction $applyExtraction,
        private readonly ReprocessUserDocumentExtractionAction $reprocessExtraction,
    ) {}

    public function show(Request $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('view', $userDocument);

        $extraction = UserDocumentExtraction::query()
            ->where('user_document_id', $userDocument->id)
            ->latest('id')
            ->first();

        if ($extraction === null) {
            return BaseResponse::ok([
                'extraction' => null,
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'extraction' => new UserDocumentExtractionResource($extraction),
        ])->toJsonResponse();
    }

    public function reprocess(Request $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('update', $userDocument);

        try {
            $extraction = $this->reprocessExtraction->execute($userDocument);
        } catch (\RuntimeException $exception) {
            return BaseResponse::unprocessableEntity(null, $exception->getMessage())->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => __('Analyse IA relancée.'),
            'extraction' => new UserDocumentExtractionResource($extraction),
        ])->toJsonResponse();
    }

    public function approve(
        ApproveUserDocumentExtractionRequest $request,
        UserDocument $userDocument,
    ): JsonResponse {
        $this->authorize('update', $userDocument);

        $extraction = UserDocumentExtraction::query()
            ->where('user_document_id', $userDocument->id)
            ->where('status', DocumentExtractionStatus::PendingReview)
            ->latest('id')
            ->firstOrFail();

        $this->applyExtraction->execute(
            $extraction,
            $request->user(),
            $request->draftOverrides(),
        );

        return BaseResponse::ok([
            'message' => __('Brouillon appliqué au profil.'),
            'extraction' => new UserDocumentExtractionResource($extraction->fresh()),
        ])->toJsonResponse();
    }

    public function reject(Request $request, UserDocument $userDocument): JsonResponse
    {
        $this->authorize('update', $userDocument);

        $extraction = UserDocumentExtraction::query()
            ->where('user_document_id', $userDocument->id)
            ->where('status', DocumentExtractionStatus::PendingReview)
            ->latest('id')
            ->firstOrFail();

        $extraction->update([
            'status' => DocumentExtractionStatus::Rejected,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        return BaseResponse::ok([
            'message' => __('Brouillon rejeté.'),
            'extraction' => new UserDocumentExtractionResource($extraction->fresh()),
        ])->toJsonResponse();
    }
}
