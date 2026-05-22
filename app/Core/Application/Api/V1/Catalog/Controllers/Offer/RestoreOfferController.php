<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Catalog\Actions\Offer\RestoreOfferAction;
use App\Core\Domain\Catalog\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RestoreOfferController extends Controller
{
    public function __construct(
        private readonly RestoreOfferAction $restoreOfferAction,
    ) {}

    public function __invoke(Offer $offer): JsonResponse
    {
        $this->restoreOfferAction->execute($offer->id);

        return BaseResponse::ok([
            'message' => __('Offre d emploi restauree avec succes.'),
        ])->toJsonResponse();
    }
}
