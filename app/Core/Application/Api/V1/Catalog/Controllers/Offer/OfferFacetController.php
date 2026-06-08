<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Offer\OfferIndexQuery;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Infrastructure\Cache\AppCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferFacetController
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        $facets = $this->cache->remember(
            $this->cache->catalogKey('offer_facets', $locale, $request->query()),
            300,
            function () use ($request) {
            return [
                'categories' => $this->facetCounts($request, 'offer_category_id'),
                'countries' => $this->facetCounts($request, 'country_id'),
                'contract_types' => $this->contractTypeFacetCounts($request),
                'work_modes' => $this->workModeFacetCounts($request),
            ];
            },
        );

        return BaseResponse::ok($facets)->toJsonResponse();
    }

    /**
     * @return array<string, int>
     */
    private function facetCounts(Request $request, string $excludeFilter): array
    {
        $facetRequest = $this->requestWithoutFilter($request, $excludeFilter);
        $idsQuery = (new OfferIndexQuery($facetRequest))
            ->published()
            ->notExpired()
            ->select('id');

        $rows = Offer::query()
            ->whereIn('id', $idsQuery)
            ->whereNotNull($excludeFilter)
            ->select($excludeFilter, DB::raw('COUNT(*) as count'))
            ->groupBy($excludeFilter)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row->{$excludeFilter}] = (int) $row->count;
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function contractTypeFacetCounts(Request $request): array
    {
        $facetRequest = $this->requestWithoutFilter($request, 'contract_type');
        $idsQuery = (new OfferIndexQuery($facetRequest))
            ->published()
            ->notExpired()
            ->select('id');

        $rows = Offer::query()
            ->whereIn('id', $idsQuery)
            ->whereNotNull('contract_type_id')
            ->select('contract_type_id', DB::raw('COUNT(*) as count'))
            ->groupBy('contract_type_id')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $locale = in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr';
        $types = ContractType::query()
            ->whereIn('id', $rows->pluck('contract_type_id'))
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($rows as $row) {
            $type = $types->get($row->contract_type_id);
            if ($type === null) {
                continue;
            }

            $name = $type->name;
            $label = is_array($name)
                ? (string) ($name[$locale] ?? $name['fr'] ?? reset($name) ?: '')
                : (string) $name;

            if ($label !== '') {
                $result[$label] = (int) $row->count;
            }
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function workModeFacetCounts(Request $request): array
    {
        $facetRequest = $this->requestWithoutFilter($request, 'work_mode');
        $idsQuery = (new OfferIndexQuery($facetRequest))
            ->published()
            ->notExpired()
            ->select('id');

        $rows = Offer::query()
            ->whereIn('id', $idsQuery)
            ->whereNotNull('work_mode')
            ->select('work_mode', DB::raw('COUNT(*) as count'))
            ->groupBy('work_mode')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row->work_mode] = (int) $row->count;
        }

        return $result;
    }

    private function requestWithoutFilter(Request $request, string $filterKey): Request
    {
        $query = $request->query();
        $filter = $query['filter'] ?? null;

        if (is_array($filter)) {
            unset($filter[$filterKey]);
            if ($filter === []) {
                unset($query['filter']);
            } else {
                $query['filter'] = $filter;
            }
        }

        unset($query['page']);

        return Request::create($request->url(), 'GET', $query);
    }
}
