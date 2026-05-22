<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Candidacy\Requests\StoreApplicationRequest;
use App\Core\Domain\Candidacy\Actions\CreateApplicationAction;
use App\Core\Domain\Candidacy\Exceptions\ApplicationEnrollmentException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApplicationController extends Controller
{
    public function __construct(
        private readonly CreateApplicationAction $createApplicationAction,
        private readonly ApplicationProgressQuery $applicationProgressQuery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Application::class);

        $user = $request->user();
        $applications = $this->applicationProgressQuery->listForUser((int) $user->id);
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
            'applications' => $applications->map(function (Application $app) use ($pick): array {
                return [
                'id' => $app->id,
                'application_number' => $app->application_number,
                'status' => $app->status instanceof \BackedEnum ? $app->status->value : $app->status,
                'offer_id' => $app->offer_id,
                'program_id' => $app->program_id,
                'offer_label' => $app->offer ? $pick($app->offer->title) : null,
                'program_label' => $app->program ? $pick($app->program->name) : null,
                'process_flow_version' => $app->process_flow_version,
                'total_due' => (float) $app->total_due,
                'total_paid' => (float) $app->total_paid,
                'current_step' => $app->currentStep ? [
                    'id' => $app->currentStep->id,
                    'step_order' => $app->currentStep->step_order,
                    'title' => $pick($app->currentStep->title),
                    'status' => $app->currentStep->status instanceof \BackedEnum
                        ? $app->currentStep->status->value
                        : $app->currentStep->status,
                ] : null,
                ];
            })->values(),
        ])->toJsonResponse();
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $this->authorize('create', Application::class);

        try {
            $application = $this->createApplicationAction->execute(
                $request->user(),
                $request->integer('offer_id') ?: null,
                $request->integer('program_id') ?: null,
                $request->integer('country_id') ?: null,
                $request->integer('process_flow_id') ?: null,
            );
        } catch (ApplicationEnrollmentException $e) {
            return BaseResponse::unprocessableEntity(message: $e->getMessage())->toJsonResponse();
        }

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::created([
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }

    public function show(Request $request, Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';
        $progress = $this->applicationProgressQuery->forApplication($application, $locale);

        return BaseResponse::ok([
            'application' => $progress->toArray(),
        ])->toJsonResponse();
    }
}
