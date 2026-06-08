<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Communication\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Communication\Actions\DispatchOfferNewslettersAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminNewsletterController extends Controller
{
    public function __construct(
        private readonly DispatchOfferNewslettersAction $dispatchNewsletters,
    ) {}

    public function sendOffers(Request $request): JsonResponse
    {
        $stats = $this->dispatchNewsletters->execute(
            $request->filled('limit') ? (int) $request->integer('limit') : null,
        );

        return BaseResponse::ok([
            'message' => __('Newsletter offres envoyée.'),
            'stats' => $stats,
        ])->toJsonResponse();
    }
}
