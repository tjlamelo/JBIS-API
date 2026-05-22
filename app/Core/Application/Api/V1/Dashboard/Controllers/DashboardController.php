<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Dashboard\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Dashboard\Services\DashboardViewResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardViewResolver $dashboardViewResolver,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('view-dashboard');

        $user = $request->user();
        $locale = str_starts_with(strtolower((string) $request->header('X-Locale', 'fr')), 'en') ? 'en' : 'fr';

        return BaseResponse::ok([
            'dashboard' => $this->dashboardViewResolver->resolve($user, $locale),
        ])->toJsonResponse();
    }
}
