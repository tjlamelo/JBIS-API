<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Drivers;

use App\Core\Domain\Shared\Export\Contracts\ExportDriverInterface;
use App\Core\Domain\Shared\Export\Contracts\ResolvedSheet;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use App\Core\Domain\Shared\Export\Support\HtmlTemplateRenderer;
use App\Core\Domain\Shared\Export\Support\PdfLayoutMetrics;
use App\Core\Domain\Shared\Screenshot\Services\ScreenshotPdfClient;
use Illuminate\Contracts\View\Factory as ViewFactory;

/**
 * Driver PDF via jbis-screenshot (Puppeteer / Chrome).
 *
 * Trois modes de templating, par ordre de priorité :
 *
 *  1) meta.template_html  (string)
 *  2) meta.template         (string : nom de vue Blade)
 *  3) Par défaut            → exports::pdf.default
 *
 * Options : meta.orientation, meta.paper
 */
final class PdfExportDriver implements ExportDriverInterface
{
    public function __construct(
        private readonly FilenameBuilder $filenames,
        private readonly ViewFactory $views,
        private readonly HtmlTemplateRenderer $htmlRenderer,
        private readonly ScreenshotPdfClient $screenshotPdfClient,
    ) {}

    public function format(): ExportFormat
    {
        return ExportFormat::Pdf;
    }

    public function supports(ExportFormat $format): bool
    {
        return $format === ExportFormat::Pdf;
    }

    public function export(ExportDefinitionDto $definition, iterable $resolvedSheets): ExportResultDto
    {
        $sheets = is_array($resolvedSheets) ? $resolvedSheets : iterator_to_array($resolvedSheets);
        $paths = $this->filenames->build($definition->fileName, ExportFormat::Pdf);
        $html = $this->resolveHtml($definition, $sheets);

        $this->writeScreenshotPdf($definition, $html, $paths['absolute_path']);

        return new ExportResultDto(
            absolutePath: $paths['absolute_path'],
            downloadFileName: $paths['download_name'],
            mimeType: ExportFormat::Pdf->mimeType(),
        );
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     */
    private function resolveHtml(ExportDefinitionDto $definition, array $sheets): string
    {
        $rawTemplate = $definition->meta['template_html'] ?? null;
        if (is_string($rawTemplate) && trim($rawTemplate) !== '') {
            $body = $this->htmlRenderer->render($rawTemplate, $definition, $sheets);

            return $this->wrapWithPdfLayout(
                $body,
                (string) ($definition->meta['title'] ?? $definition->fileName),
            );
        }

        $bladeView = $this->resolveBladeView($definition);
        if (! $this->views->exists($bladeView)) {
            throw new \RuntimeException("Template Blade introuvable pour l'export PDF : « {$bladeView} »");
        }

        return $this->views->make($bladeView, [
            'definition' => $definition,
            'sheets' => $this->materializeSheets($sheets),
            'meta' => $definition->meta,
            'generatedAt' => now(),
        ])->render();
    }

    private function wrapWithPdfLayout(string $bodyHtml, string $title): string
    {
        if (str_contains($bodyHtml, 'jbis-pdf-content')) {
            return $bodyHtml;
        }

        if (! $this->views->exists('pdf.wrapper')) {
            return $bodyHtml;
        }

        return $this->views->make('pdf.wrapper', [
            'bodyHtml' => $bodyHtml,
            'title' => $title,
        ])->render();
    }

    private function resolveBladeView(ExportDefinitionDto $definition): string
    {
        return (string) ($definition->meta['template'] ?? 'exports::pdf.default');
    }

    private function writeScreenshotPdf(ExportDefinitionDto $definition, string $html, string $absolutePath): void
    {
        $paper = (string) ($definition->meta['paper'] ?? config('export-pdf.paper', 'a4'));
        $pdfBinary = $this->screenshotPdfClient->htmlToPdf(
            $html,
            $paper,
            PdfLayoutMetrics::printMarginsMm(),
        );

        if (file_put_contents($absolutePath, $pdfBinary) === false) {
            throw new \RuntimeException('Impossible d\'écrire le fichier PDF exporté.');
        }
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     * @return array<int, array{name:string, headers:array<int,string>, rows:array<int,array<string,mixed>>}>
     */
    private function materializeSheets(array $sheets): array
    {
        $payload = [];
        foreach ($sheets as $resolved) {
            $rows = [];
            $keys = $resolved->fieldKeys();
            foreach ($resolved->rows() as $row) {
                $ordered = [];
                foreach ($keys as $k) {
                    $ordered[$k] = $row[$k] ?? null;
                }
                $rows[] = $ordered;
            }

            $payload[] = [
                'name' => $resolved->sheet->name,
                'headers' => $resolved->headers(),
                'rows' => $rows,
            ];
        }

        return $payload;
    }
}
