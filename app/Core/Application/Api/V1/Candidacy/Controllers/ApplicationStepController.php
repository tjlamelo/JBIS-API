<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Candidacy\Requests\DeclareStepPaymentRequest;
use App\Core\Domain\Candidacy\Actions\RecordApplicationStepPaymentAction;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApplicationStepController extends Controller
{
    public function __construct(
        private readonly RecordApplicationStepPaymentAction $recordPaymentAction,
        private readonly ApplicationProgressQuery $applicationProgressQuery,
    ) {}

    public function declarePayment(
        DeclareStepPaymentRequest $request,
        Application $application,
        ApplicationStep $step,
    ): JsonResponse {
        $this->authorize('view', $application);
        $this->assertStepBelongsToApplication($application, $step);

        if ((int) $application->user_id !== (int) $request->user()?->id) {
            abort(403);
        }

        if ((float) $step->amount_due <= 0) {
            return BaseResponse::unprocessableEntity(
                message: __('Cette étape ne requiert pas de paiement.'),
            )->toJsonResponse();
        }

        $this->recordPaymentAction->execute(
            $step,
            (float) $request->input('amount'),
            (string) $request->input('payment_type', 'PARTIAL'),
            'PENDING',
            $request->input('reference'),
            (int) $request->user()->id,
            (string) $request->input('payment_method', 'BANK_TRANSFER'),
            $request->input('description'),
        );

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
