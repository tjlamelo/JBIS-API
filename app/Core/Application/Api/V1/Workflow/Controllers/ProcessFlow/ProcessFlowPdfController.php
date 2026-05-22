<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow;

use App\Core\Domain\Workflow\Actions\ProcessFlow\GenerateProcessFlowPdfAction;
use App\Core\Domain\Workflow\Exceptions\ProcessFlowPdfGenerationException;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ProcessFlowPdfController extends Controller
{
    public function __construct(
        private readonly GenerateProcessFlowPdfAction $generateProcessFlowPdfAction,
    ) {}

    public function __invoke(Request $request, ProcessFlow $processFlow): BinaryFileResponse|JsonResponse
    {
        $this->authorize('view', $processFlow);

        $locale = $request->query('locale', $request->header('X-Locale', 'fr'));
        $locale = str_starts_with(strtolower((string) $locale), 'en') ? 'en' : 'fr';

        try {
            $result = $this->generateProcessFlowPdfAction->execute($processFlow, $locale);
        } catch (ProcessFlowPdfGenerationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        return response()
            ->download($result->absolutePath, $result->downloadFileName, [
                'Content-Type' => $result->mimeType,
            ])
            ->deleteFileAfterSend(true);
    }
}
