<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Services\ProcessFlow\Contracts;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPdfResult;
use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPrintViewModel;

interface ProcessFlowPdfRenderer
{
    public function render(ProcessFlowPrintViewModel $viewModel, string $fileBaseName): ProcessFlowPdfResult;
}
