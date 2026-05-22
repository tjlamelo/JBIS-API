<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Services\ProcessFlow;

use App\Core\Domain\Shared\Screenshot\Exceptions\ScreenshotServiceException;
use App\Core\Domain\Shared\Screenshot\Services\ScreenshotPdfClient;
use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPdfResult;
use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPrintViewModel;
use App\Core\Domain\Workflow\Exceptions\ProcessFlowPdfGenerationException;
use App\Core\Domain\Workflow\Services\ProcessFlow\Contracts\ProcessFlowPdfRenderer;
use Illuminate\Support\Str;

final class ProcessFlowScreenshotPdfRenderer implements ProcessFlowPdfRenderer
{
    public function __construct(
        private readonly ProcessFlowPdfHtmlBuilder $htmlBuilder,
        private readonly ScreenshotPdfClient $screenshotPdfClient,
    ) {}

    public function render(ProcessFlowPrintViewModel $viewModel, string $fileBaseName): ProcessFlowPdfResult
    {
        $html = $this->htmlBuilder->build($viewModel);
        $paper = (string) config('process-flow-pdf.paper', 'a4');
        $margins = config('process-flow-pdf.margins', []);

        try {
            $pdf = $this->screenshotPdfClient->htmlToPdf($html, $paper, is_array($margins) ? $margins : []);
        } catch (ScreenshotServiceException $e) {
            throw ProcessFlowPdfGenerationException::chromeUnavailable($e->getMessage());
        }

        return $this->persistPdf($pdf, $viewModel, $fileBaseName);
    }

    private function persistPdf(string $pdf, ProcessFlowPrintViewModel $viewModel, string $fileBaseName): ProcessFlowPdfResult
    {
        $slug = Str::slug($fileBaseName) ?: 'process-flow';
        $fileName = $slug.'-v'.$viewModel->version.'-'.now()->format('Ymd-His').'.pdf';
        $absolutePath = storage_path('app/temp/'.$fileName);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        file_put_contents($absolutePath, $pdf);

        return new ProcessFlowPdfResult(
            absolutePath: $absolutePath,
            downloadFileName: $fileName,
        );
    }
}
