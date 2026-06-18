<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Offer\OfferIndexQuery;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferResource;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferShortResource;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Infrastructure\Cache\AppCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicOfferController extends Controller
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function index(OfferIndexQuery $query, Request $request): JsonResponse
    {
        $locale = app()->getLocale();
        $useCache = ! $request->filled('filter.search')
            && (int) $request->integer('page', 1) === 1;

        if ($useCache) {
            $payload = $this->cache->remember(
                $this->cache->catalogKey('offers_index', $locale, $request->query()),
                60,
                fn () => $this->buildIndexPayload($query, $request),
            );

            return BaseResponse::ok($payload)->toJsonResponse();
        }

        return BaseResponse::ok($this->buildIndexPayload($query, $request))->toJsonResponse();
    }

    public function show(string $slug): JsonResponse
    {
        $locale = app()->getLocale();

        $payload = $this->cache->remember(
            $this->cache->catalogKey('offer_show', $locale, $slug),
            300,
            function () use ($slug): ?array {
                $offer = Offer::query()
                    ->with([
                        'program.requiredDocuments',
                        'company',
                        'country',
                        'category',
                        'trade',
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
                    return null;
                }

                return [
                    'offer' => (new OfferResource($offer))->resolve(),
                ];
            },
        );

        if ($payload === null) {
            return BaseResponse::notFound([
                'message' => __('Offre d\'emploi introuvable ou expirée.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok($payload)->toJsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndexPayload(OfferIndexQuery $query, Request $request): array
    {
        $query->published()->notExpired();

        $offers = $query->paginate((int) $request->integer('per_page', 15))
            ->appends($request->query());

        return [
            'offers' => OfferShortResource::collection($offers)->resolve(),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total(),
            ],
        ];
    }
}
