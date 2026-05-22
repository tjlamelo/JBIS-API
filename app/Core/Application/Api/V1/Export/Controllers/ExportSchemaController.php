<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Export\Controllers;

use App\Core\Domain\Shared\Export\Services\ExportSchemaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * GET /api/v1/exports/schema
 *
 * Renvoie le catalogue des sources/champs/formats disponibles pour
 * construire dynamiquement un formulaire d'export côté front.
 */
final class ExportSchemaController extends Controller
{
    public function __construct(private readonly ExportSchemaService $service) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_OK,
            'message' => 'OK',
            'data' => $this->service->toArray(),
        ]);
    }
}
