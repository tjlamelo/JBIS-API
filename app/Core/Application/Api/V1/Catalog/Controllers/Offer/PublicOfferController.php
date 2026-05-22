<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Offer\OfferIndexQuery;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferResource;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferShortResource;
use App\Core\Domain\Catalog\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicOfferController extends Controller
{
    public function index(OfferIndexQuery $query, Request $request): JsonResponse
    {
        // 1. Sécurité Domaine
        $query->published()->notExpired();

        // 2. Tri par pertinence si recherche active (via Builder)
        if ($request->filled('filter.search')) {
            // Le tri est déjà géré dans le OfferBuilder->search()
            // On s'assure juste ici de ne pas avoir de conflits
        }

        // 3. Pagination (Prend en compte les relations définies dans OfferIndexQuery)
        $offers = $query->paginate((int) $request->integer('per_page', 15))
            ->appends($request->query());

        return BaseResponse::ok([
            'offers' => OfferShortResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(string $slug): JsonResponse
    {
        $offer = Offer::query()

            ->with([
                'program.requiredDocuments',
                'company',
                'country',
                'category',
                'city.region',
                'contractType',
                'benefits',
                'requiredDocuments',
            ])
            ->published()
            ->notExpired()
            ->where(function ($query) use ($slug) {
                $query->where('slug->fr', $slug)
                    ->orWhere('slug->en', $slug);
            })
            ->first();

        if (! $offer) {
            return BaseResponse::notFound([
                'message' => __('Offre d\'emploi introuvable ou expirée.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'offer' => new OfferResource($offer),
        ])->toJsonResponse();
    }
}
