<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Catalog\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OfferApplicationReadinessController extends Controller
{
    public function __construct(
        private readonly OfferApplicationReadinessService $readinessService,
    ) {}

    public function show(Request $request, Offer $offer): JsonResponse
    {
        $readiness = $this->readinessService->assess($offer, $request->user());

        return BaseResponse::ok([
            'readiness' => $readiness->toArray(),
        ])->toJsonResponse();
    }
}
