<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Program;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Infrastructure\Cache\AppCache;
use Exception;
use Illuminate\Http\JsonResponse;

class ProgramFilterController
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            $locale = app()->getLocale();

            $payload = $this->cache->remember(
                $this->cache->catalogKey('program_filters', $locale),
                3600,
                fn () => [
                    'zones' => GeographicZone::query()
                        ->whereHas('programs', function ($query) {
                            $query->active();
                        })
                        ->get()
                        ->map(fn ($zone) => [
                            'label' => (string) $zone->name,
                            'value' => (string) $zone->id,
                        ])->values()->all(),
                ],
            );

            return BaseResponse::ok($payload)->toJsonResponse();
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Erreur lors de la génération des filtres de programmes',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
}
