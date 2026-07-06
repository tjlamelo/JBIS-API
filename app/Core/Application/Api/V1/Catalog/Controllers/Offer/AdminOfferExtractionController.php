<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Requests\Offer\ExtractOfferFromTextRequest;
use App\Core\Domain\Catalog\Actions\Offer\ExtractOfferFromTextAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminOfferExtractionController extends Controller
{
    public function __construct(
        private readonly ExtractOfferFromTextAction $extractOfferFromTextAction,
    ) {}

    public function __invoke(ExtractOfferFromTextRequest $request): JsonResponse
    {
        $draft = $this->extractOfferFromTextAction->execute(
            (string) $request->validated('raw_text'),
            $request->formContext(),
            $request->scope(),
        );

        return BaseResponse::ok([
            'message' => __('Brouillon d\'offre généré.'),
            'draft' => $draft,
        ])->toJsonResponse();
    }
}
