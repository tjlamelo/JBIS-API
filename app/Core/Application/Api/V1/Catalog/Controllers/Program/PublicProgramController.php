<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Program;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Program\ProgramIndexQuery;
use App\Core\Application\Api\V1\Catalog\Resources\Program\ProgramResource;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Infrastructure\Cache\AppCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProgramController extends Controller
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function index(ProgramIndexQuery $query, Request $request): JsonResponse
    {
        $locale = app()->getLocale();
        $useCache = (int) $request->integer('page', 1) === 1;

        if ($useCache) {
            $payload = $this->cache->remember(
                $this->cache->catalogKey('programs_index', $locale, $request->query()),
                120,
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
            $this->cache->catalogKey('program_show', $locale, $slug),
            300,
            function () use ($slug): ?array {
                $program = Program::query()
                    ->active()
                    ->validDates()
                    ->with([
                        'requiredDocuments',
                        'geographicZone',
                        'offers' => fn ($q) => $q->published(),
                    ])
                    ->where('slug->'.app()->getLocale(), $slug)
                    ->orWhere('slug->fr', $slug)
                    ->orWhere('slug->en', $slug)
                    ->first();

                if (! $program) {
                    return null;
                }

                return [
                    'program' => (new ProgramResource($program))->resolve(),
                ];
            },
        );

        if ($payload === null) {
            return BaseResponse::notFound([
                'message' => __('Ce programme est introuvable ou n\'est plus disponible.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok($payload)->toJsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndexPayload(ProgramIndexQuery $query, Request $request): array
    {
        $programs = $query->active()
            ->validDates()
            ->withCount(['offers' => fn ($q) => $q->published()])
            ->paginate((int) $request->integer('per_page', 12))
            ->appends($request->query());

        return [
            'programs' => ProgramResource::collection($programs)->resolve(),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
            ],
        ];
    }
}
