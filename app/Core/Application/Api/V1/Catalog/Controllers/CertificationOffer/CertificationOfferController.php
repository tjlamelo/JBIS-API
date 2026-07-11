<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\CertificationOffer;

use App\Core\Application\Api\V1\Catalog\Resources\CertificationOffer\CertificationOfferResource;
use App\Core\Domain\Catalog\Models\CertificationOffer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CertificationOfferController extends Controller
{
    /**
     * Catalogue certifications AMCA actives (site public).
     */
    public function index(Request $request): JsonResponse
    {
        $items = CertificationOffer::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $registrationFee = $items->first()?->registration_fee;

        return response()->json([
            'data' => [
                'registration_fee' => $registrationFee,
                'certification_offers' => CertificationOfferResource::collection($items),
            ],
        ]);
    }

    public function show(CertificationOffer $certificationOffer): JsonResponse
    {
        if (! $certificationOffer->is_active) {
            abort(404);
        }

        return response()->json([
            'data' => new CertificationOfferResource($certificationOffer),
        ]);
    }
}
