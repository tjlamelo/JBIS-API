<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Program;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Program\ProgramIndexQuery;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Infrastructure\Cache\AppCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramFacetController
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        $facets = $this->cache->remember(
            $this->cache->catalogKey('program_facets', $locale, $request->query()),
            300,
            function () use ($request) {
            return [
                'zones' => $this->facetCounts($request, 'geographic_zone_id'),
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
        $idsQuery = (new ProgramIndexQuery($facetRequest))
            ->public()
            ->select('id');

        $rows = Program::query()
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
