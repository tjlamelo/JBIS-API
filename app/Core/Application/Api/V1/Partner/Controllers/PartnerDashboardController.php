<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Partner\Actions\GetPartnerDashboardStatsAction;
use App\Core\Domain\Partner\Support\PartnerAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerDashboardController extends Controller
{
    public function __construct(
        private readonly PartnerAccess $access,
        private readonly GetPartnerDashboardStatsAction $stats,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            return BaseResponse::ok([
                'stats' => [
                    'students_total' => 0,
                    'documents_complete' => 0,
                    'placements_validated' => 0,
                    'cohorts_active' => 0,
                ],
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'stats' => $this->stats->execute($organization),
        ])->toJsonResponse();
    }
}
