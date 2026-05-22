<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPdfResult;
use App\Core\Domain\Workflow\Mappers\ProcessFlow\ProcessFlowPrintMapper;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Services\ProcessFlow\Contracts\ProcessFlowPdfRenderer;
use Illuminate\Support\Str;

final class GenerateProcessFlowPdfAction
{
    public function __construct(
        private readonly ProcessFlowPrintMapper $printMapper,
        private readonly ProcessFlowPdfRenderer $pdfRenderer,
    ) {}

    public function execute(ProcessFlow $flow, string $locale = 'fr'): ProcessFlowPdfResult
    {
        $viewModel = $this->printMapper->map($flow, $locale);
        $baseName = Str::slug($viewModel->title) ?: 'process-flow-'.$flow->id;

        return $this->pdfRenderer->render($viewModel, $baseName);
    }
}
