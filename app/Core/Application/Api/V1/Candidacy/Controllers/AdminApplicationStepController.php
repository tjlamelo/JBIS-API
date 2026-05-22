<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Candidacy\Requests\AdvanceApplicationStepRequest;
use App\Core\Application\Api\V1\Candidacy\Requests\RecordStepPaymentRequest;
use App\Core\Application\Api\V1\Candidacy\Requests\UpsertApplicationInterviewRequest;
use App\Core\Application\Api\V1\Candidacy\Requests\ValidateApplicationStepRequest;
use App\Core\Domain\Candidacy\Actions\AdvanceApplicationStepAction;
use App\Core\Domain\Candidacy\Actions\RecordApplicationStepPaymentAction;
use App\Core\Domain\Candidacy\Actions\UpsertApplicationInterviewAction;
use App\Core\Domain\Candidacy\Exceptions\ApplicationStepAdvanceException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Services\InterviewStepSyncService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class AdminApplicationStepController extends Controller
{
    public function __construct(
        private readonly AdvanceApplicationStepAction $advanceApplicationStepAction,
        private readonly RecordApplicationStepPaymentAction $recordPaymentAction,
        private readonly ApplicationProgressQuery $applicationProgressQuery,
        private readonly ApplicationActivityLogger $activityLogger,
        private readonly InterviewStepSyncService $interviewStepSync,
        private readonly UpsertApplicationInterviewAction $upsertInterviewAction,
    ) {}

    public function upsertInterview(
        UpsertApplicationInterviewRequest $request,
        Application $application,
        ApplicationStep $step,
    ): JsonResponse {
        $this->authorize('update', $application);
        $this->assertStepBelongsToApplication($application, $step);

        $this->upsertInterviewAction->execute(
            $step,
            $request->validated(),
            (int) $request->user()->id,
        );

        return $this->progressResponse($request, $application->fresh());
    }

    public function validateStep(ValidateApplicationStepRequest $request, Application $application, ApplicationStep $step): JsonResponse
    {
        $this->authorize('update', $application);
        $this->assertStepBelongsToApplication($application, $step);

        $data = $request->validated();
        $staffId = (int) $request->user()->id;
        $now = Carbon::now();

        if (array_key_exists('documents_validated', $data)) {
            $step->update([
                'documents_validated' => (bool) $data['documents_validated'],
                'documents_validated_at' => $data['documents_validated'] ? $now : null,
                'documents_validated_by' => $data['documents_validated'] ? $staffId : null,
            ]);
            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_DOCUMENTS_VALIDATED,
                $step->id,
                $staffId,
                ['documents_validated' => (bool) $data['documents_validated']],
            );
        }

        if (array_key_exists('interview_passed', $data)) {
            $passed = $data['interview_passed'];
            $step->update([
                'interview_passed' => $passed,
                'interview_validated_at' => $passed !== null ? $now : null,
                'interview_validated_by' => $passed !== null ? $staffId : null,
            ]);
            $this->interviewStepSync->syncFromStep($step->fresh(), $passed === null ? null : (bool) $passed);
            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_INTERVIEW_VALIDATED,
                $step->id,
                $staffId,
                ['interview_passed' => $passed],
            );
        }

        if (array_key_exists('is_signed', $data)) {
            $step->update([
                'is_signed' => (bool) $data['is_signed'],
                'signed_at' => $data['is_signed'] ? $now : null,
            ]);
            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_SIGNATURE_VALIDATED,
                $step->id,
                $staffId,
                ['is_signed' => (bool) $data['is_signed']],
            );
        }

        return $this->progressResponse($request, $application->fresh());
    }

    public function recordPayment(RecordStepPaymentRequest $request, Application $application, ApplicationStep $step): JsonResponse
    {
        $this->authorize('update', $application);
        $this->assertStepBelongsToApplication($application, $step);

        $this->recordPaymentAction->execute(
            $step,
            (float) $request->input('amount'),
            (string) $request->input('payment_type', 'FULL'),
            (string) $request->input('status', 'COMPLETED'),
            $request->input('reference'),
            (int) $request->user()->id,
        );

        return $this->progressResponse($request, $application->fresh());
    }

    public function advance(AdvanceApplicationStepRequest $request, Application $application, ApplicationStep $step): JsonResponse
    {
        $this->authorize('update', $application);
        $this->assertStepBelongsToApplication($application, $step);

        try {
            $this->advanceApplicationStepAction->execute(
                $step,
                (int) $request->user()->id,
                (bool) $request->boolean('force'),
            );
        } catch (ApplicationStepAdvanceException $e) {
            return BaseResponse::unprocessableEntity(message: $e->getMessage())->toJsonResponse();
        }

        return $this->progressResponse($request, $application->fresh());
    }

    private function assertStepBelongsToApplication(Application $application, ApplicationStep $step): void
    {
        if ($step->application_id !== $application->id) {
            abort(404);
        }
    }

    private function progressResponse(Request $request, Application $application): JsonResponse
    {
        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok(['application' => $progress->toArray()])->toJsonResponse();
    }
}
