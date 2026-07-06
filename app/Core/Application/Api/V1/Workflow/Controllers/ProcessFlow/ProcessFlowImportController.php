<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow\ImportProcessFlowRequest;
use App\Core\Application\Api\V1\Workflow\Resources\ProcessFlow\ProcessFlowResource;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportContext;
use App\Core\Domain\Workflow\Import\ProcessFlowImportCoordinator;
use App\Core\Domain\Workflow\Import\ProcessFlowImportTemplateGenerator;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ProcessFlowImportController extends Controller
{
    public function __construct(
        private readonly ProcessFlowImportCoordinator $coordinator,
        private readonly ProcessFlowImportTemplateGenerator $templateGenerator,
    ) {}

    public function import(ImportProcessFlowRequest $request): JsonResponse
    {
        $this->authorize('create', ProcessFlow::class);

        $uploaded = $request->file('file');
        $format = $request->resolvedFormat();
        $commit = $request->boolean('commit');

        $storedPath = $uploaded->storeAs(
            'imports/process-flows',
            uniqid('import_', true).'.'.($format === 'json' ? 'json' : 'xlsx'),
        );

        $absolutePath = Storage::path($storedPath);

        try {
            $result = $this->coordinator->importFromFile(
                $absolutePath,
                $format,
                $commit,
                new ProcessFlowImportContext(importedByUserId: (int) $request->user()?->id ?: null),
            );
        } finally {
            Storage::delete($storedPath);
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity(
                data: $result->toArray(),
                message: __('Le fichier d\'import contient des erreurs.'),
            )->toJsonResponse();
        }

        $payload = $result->toArray();

        if ($commit && $result->createdFlowIds !== []) {
            $flows = ProcessFlow::query()
                ->whereIn('id', $result->createdFlowIds)
                ->with(['country', 'sections.steps.documentTypes', 'steps.documentTypes'])
                ->get();

            $payload['process_flows'] = ProcessFlowResource::collection($flows);
        }

        return BaseResponse::ok([
            'message' => $commit
                ? __('Parcours importé(s) avec succès.')
                : __('Validation réussie — aucune donnée écrite (dry-run).'),
            'import' => $payload,
        ])->toJsonResponse();
    }

    public function template(): BinaryFileResponse
    {
        $this->authorize('create', ProcessFlow::class);

        $path = $this->templateGenerator->defaultPath();
        $this->templateGenerator->generate($path);

        return response()->download(
            $path,
            'process-flow-import-template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
