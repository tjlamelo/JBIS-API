<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Core\Domain\Identity\Support\UserPersonName;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationProgressQuery $applicationProgressQuery,
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
                'offer:id,title',
                'program:id,name',
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
            'applications' => $paginator->getCollection()->map(static function (Application $app) use ($pick): array {
                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'status' => $app->status instanceof \BackedEnum ? $app->status->value : $app->status,
                    'user' => $app->user ? UserPersonName::toContactArray($app->user) : null,
                    'offer_label' => $app->offer ? $pick($app->offer->title) : null,
                    'program_label' => $app->program ? $pick($app->program->name) : null,
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
}
