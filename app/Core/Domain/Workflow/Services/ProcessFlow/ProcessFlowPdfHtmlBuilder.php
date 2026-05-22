<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Services\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPrintViewModel;
use Illuminate\Contracts\View\Factory as ViewFactory;

final class ProcessFlowPdfHtmlBuilder
{
    public function __construct(
        private readonly ViewFactory $views,
    ) {}

    public function build(ProcessFlowPrintViewModel $viewModel): string
    {
        return $this->views->make('workflow.pdf.process-flow', $viewModel->toArray())->render();
    }
}
