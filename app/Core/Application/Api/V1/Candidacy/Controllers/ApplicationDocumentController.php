<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Candidacy\Requests\AttachApplicationDocumentRequest;
use App\Core\Application\Api\V1\Candidacy\Requests\ReviewApplicationDocumentRequest;
use App\Core\Domain\Candidacy\Actions\AttachApplicationDocumentAction;
use App\Core\Domain\Candidacy\Actions\ReviewApplicationDocumentAction;
use App\Core\Domain\Candidacy\Exceptions\ApplicationDocumentException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApplicationDocumentController extends Controller
{
    public function __construct(
        private readonly AttachApplicationDocumentAction $attachAction,
        private readonly ReviewApplicationDocumentAction $reviewAction,
        private readonly ApplicationProgressQuery $applicationProgressQuery,
    ) {}

    public function attach(
        AttachApplicationDocumentRequest $request,
        Application $application,
        ApplicationStep $step,
    ): JsonResponse {
        $this->authorize('view', $application);
        $this->assertStepBelongsToApplication($application, $step);

        try {
            $document = $this->attachAction->execute(
                $application,
                $step,
                $request->user(),
                $request->integer('user_document_id'),
            );
        } catch (ApplicationDocumentException $e) {
            return BaseResponse::unprocessableEntity(message: $e->getMessage())->toJsonResponse();
        }

        return $this->progressResponse($request, $application->fresh(), [
            'application_document' => $this->mapDocument($document),
        ]);
    }

    public function review(
        ReviewApplicationDocumentRequest $request,
        Application $application,
        ApplicationDocument $applicationDocument,
    ): JsonResponse {
        $this->authorize('update', $application);

        if ($applicationDocument->application_id !== $application->id) {
            abort(404);
        }

        $document = $this->reviewAction->execute(
            $applicationDocument,
            (string) $request->input('status'),
            (int) $request->user()->id,
            $request->input('admin_notes'),
        );

        return $this->progressResponse($request, $application->fresh(), [
            'application_document' => $this->mapDocument($document),
        ]);
    }

    private function assertStepBelongsToApplication(Application $application, ApplicationStep $step): void
    {
        if ($step->application_id !== $application->id) {
            abort(404);
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function progressResponse(Request $request, Application $application, array $extra = []): JsonResponse
    {
        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok([
            'application' => $progress->toArray(),
            ...$extra,
        ])->toJsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDocument(ApplicationDocument $document): array
    {
        $userDoc = $document->userDocument;

        return [
            'id' => $document->id,
            'application_step_id' => $document->application_step_id,
            'user_document_id' => $document->user_document_id,
            'status' => $document->status,
            'admin_notes' => $document->admin_notes,
            'reviewed_at' => $document->reviewed_at?->toIso8601String(),
            'document_type' => $userDoc?->documentType ? [
                'id' => $userDoc->documentType->id,
                'name' => $userDoc->documentType->name,
            ] : null,
        ];
    }
}
