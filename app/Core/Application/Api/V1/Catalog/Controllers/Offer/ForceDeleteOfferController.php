<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Catalog\Actions\ForceDeleteOfferAction;
use App\Core\Domain\Catalog\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ForceDeleteOfferController extends Controller
{
    public function __construct(
        private readonly ForceDeleteOfferAction $forceDeleteOfferAction,
    ) {}

    public function __invoke(Offer $offer): JsonResponse
    {
        $this->forceDeleteOfferAction->execute($offer->id);

        return BaseResponse::ok([
            'message' => __('Offre d emploi supprimee definitivement avec succes.'),
        ])->toJsonResponse();
    }
}
