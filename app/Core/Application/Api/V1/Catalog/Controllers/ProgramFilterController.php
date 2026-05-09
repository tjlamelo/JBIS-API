<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Location\Models\GeographicZone;
use Illuminate\Http\JsonResponse;
use Exception;

class ProgramFilterController
{
    public function __invoke(): JsonResponse
    {
        try {
            // On récupère les zones qui ont au moins un programme actif (scope active)
            // Cela évite d'afficher des filtres vides pour l'utilisateur
            $zones = GeographicZone::query()
                ->whereHas('programs', function ($query) {
                    $query->active();
                })
                ->get()
                ->map(fn($zone) => [
                    // Spatie gère automatiquement la traduction selon app()->getLocale()
                    'label' => (string) $zone->name, 
                    'value' => (string) $zone->id,
                    'icon'  => $zone->icon
                ]);

            return BaseResponse::ok([
                'zones' => $zones,
            ])->toJsonResponse();

        } catch (Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Erreur lors de la génération des filtres de programmes',
                'debug'   => $e->getMessage()
            ], 500);
        }
    }
}