<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Export\Controllers;

use App\Core\Application\Api\V1\Export\Requests\ExportRequest;
use App\Core\Domain\Shared\Export\Exceptions\ExportException;
use App\Core\Domain\Shared\Export\Services\ExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Endpoint principal : déclenche un export et renvoie le fichier en téléchargement.
 *
 * POST /api/v1/exports
 *
 * Le payload est une définition d'export (format, file_name, sheets, meta).
 * En cas de format/source/champ invalide, une réponse JSON 422 est renvoyée.
 *
 * Le fichier généré est stocké dans storage/app/exports puis supprimé
 * automatiquement après l'envoi via `deleteFileAfterSend()`.
 */
final class ExportController extends Controller
{
    public function __construct(private readonly ExportService $service) {}

    public function __invoke(ExportRequest $request): BinaryFileResponse|JsonResponse
    {
        try {
            $result = $this->service->export($request->toDefinition());
        } catch (ExportException $e) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $e->getMessage(),
                'data' => null,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()
            ->download($result->absolutePath, $result->downloadFileName, [
                'Content-Type' => $result->mimeType,
            ])
            ->deleteFileAfterSend(true);
    }
}
