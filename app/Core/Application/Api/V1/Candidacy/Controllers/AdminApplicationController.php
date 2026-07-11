<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Candidacy\Actions\CancelApplicationAction;
use App\Core\Domain\Candidacy\Actions\RejectApplicationAction;
use App\Core\Domain\Candidacy\Actions\ResumePendingApplicationsAction;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Identity\Support\UserPersonName;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationProgressQuery $applicationProgressQuery,
        private readonly RejectApplicationAction $rejectApplicationAction,
        private readonly CancelApplicationAction $cancelApplicationAction,
        private readonly ResumePendingApplicationsAction $resumePendingApplicationsAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manageAny', Application::class);

        $search = trim((string) $request->input('search', ''));
        $perPage = min(50, max(1, (int) $request->input('per_page', 20)));

        $query = Application::query()
            ->with([
                ...UserPersonName::withProfile('user'),
                'currentStep:id,application_id,step_order,title,status',
                'offer:id,trade_id',
                'offer.trade:id,name',
                'program:id,name',
                'processFlow:id,name,version,offer_id',
            ])
            ->orderByDesc('updated_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")));
            });
        }

        $paginator = $query->paginate($perPage);

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $pick = static function (array|string|null $field) use ($locale): ?string {
            if ($field === null) {
                return null;
            }
            if (is_string($field)) {
                return $field;
            }

            return $field[$locale] ?? $field['fr'] ?? $field['en'] ?? null;
        };

        return BaseResponse::ok([
            'applications' => $paginator->getCollection()->map(static function (Application $app) use ($pick, $locale): array {
                $status = $app->status instanceof \BackedEnum ? $app->status->value : $app->status;

                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'status' => $status,
                    'status_label' => ApplicationStatus::tryFrom((string) $status)?->label($locale) ?? (string) $status,
                    'user' => $app->user ? UserPersonName::toContactArray($app->user) : null,
                    'offer_id' => $app->offer_id,
                    'offer_label' => $app->offer ? $pick($app->offer->resolvedTitleTranslations()) : null,
                    'program_label' => $app->program ? $pick($app->program->name) : null,
                    'process_flow_id' => $app->process_flow_id,
                    'process_flow_version' => (int) $app->process_flow_version,
                    'process_flow_label' => $app->processFlow ? $pick($app->processFlow->name) : null,
                    'current_step' => $app->currentStep ? [
                        'id' => $app->currentStep->id,
                        'step_order' => $app->currentStep->step_order,
                        'title' => $pick($app->currentStep->title),
                        'status' => $app->currentStep->status instanceof \BackedEnum
                            ? $app->currentStep->status->value
                            : $app->currentStep->status,
                    ] : null,
                    'total_due' => (float) $app->total_due,
                    'total_paid' => (float) $app->total_paid,
                    'updated_at' => $app->updated_at?->toIso8601String(),
                ];
            })->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ])->toJsonResponse();
    }

    public function show(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok([
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }

    public function reject(Request $request, Application $application): JsonResponse
    {
        $this->authorize('reject', $application);

        try {
            $application = $this->rejectApplicationAction->execute(
                $application,
                $request->user(),
                $request->input('reason') ? (string) $request->input('reason') : null,
            );
        } catch (\InvalidArgumentException $e) {
            return BaseResponse::unprocessableEntity(message: $e->getMessage())->toJsonResponse();
        }

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok([
            'message' => __('Candidature refusée.'),
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }

    public function cancel(Request $request, Application $application): JsonResponse
    {
        $this->authorize('cancel', $application);

        try {
            $application = $this->cancelApplicationAction->execute(
                $application,
                $request->user(),
                $request->input('reason') ? (string) $request->input('reason') : null,
            );
        } catch (\InvalidArgumentException $e) {
            return BaseResponse::unprocessableEntity(message: $e->getMessage())->toJsonResponse();
        }

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok([
            'message' => __('Candidature annulée.'),
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }

    public function resume(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (! $this->resumePendingApplicationsAction->resumeApplication($application)) {
            return BaseResponse::unprocessableEntity(
                message: __('Impossible de démarrer le parcours : des documents obligatoires du candidat sont encore en attente de validation.'),
            )->toJsonResponse();
        }

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application->fresh(), $locale);

        return BaseResponse::ok([
            'message' => __('Parcours démarré.'),
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }
}
