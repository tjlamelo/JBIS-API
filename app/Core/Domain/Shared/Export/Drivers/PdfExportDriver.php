<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Drivers;

use App\Core\Domain\Shared\Export\Contracts\ExportDriverInterface;
use App\Core\Domain\Shared\Export\Contracts\ResolvedSheet;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use App\Core\Domain\Shared\Export\Exceptions\MissingDependencyException;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use App\Core\Domain\Shared\Export\Support\HtmlTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Contracts\View\Factory as ViewFactory;

/**
 * Driver PDF basé sur barryvdh/laravel-dompdf.
 *
 * Trois modes de templating, par ordre de priorité :
 *
 *  1) meta.template_html  (string)
 *     → HTML brut envoyé par le front, avec placeholders {{ key }}.
 *       Le front maîtrise totalement la mise en page (logo, signatures,
 *       polices, CSS print…) et le module n'injecte que les données.
 *       Voir HtmlTemplateRenderer pour la syntaxe.
 *
 *  2) meta.template       (string : nom de vue Blade)
 *     → Template Blade nommé, livré côté API (ex. "exports::pdf.default"
 *       ou un template projet "exports.pdf.candidat-rapport").
 *
 *  3) Par défaut          → template intégré "exports::pdf.default".
 *
 * Options communes :
 *   - meta.orientation : "portrait" (défaut) | "landscape"
 *   - meta.paper       : "a4" (défaut), "letter", etc.
 */
final class PdfExportDriver implements ExportDriverInterface
{
    public function __construct(
        private readonly FilenameBuilder $filenames,
        private readonly ViewFactory $views,
        private readonly HtmlTemplateRenderer $htmlRenderer,
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
        if (! class_exists(DomPdf::class)) {
            throw MissingDependencyException::forPackage('pdf', 'barryvdh/laravel-dompdf');
        }

        $sheets = is_array($resolvedSheets) ? $resolvedSheets : iterator_to_array($resolvedSheets);

        $html = $this->resolveHtml($definition, $sheets);

        $orientation = (string) ($definition->meta['orientation'] ?? 'portrait');
        $paper = (string) ($definition->meta['paper'] ?? 'a4');

        $pdf = DomPdf::loadHTML($html)->setPaper($paper, $orientation);

        $paths = $this->filenames->build($definition->fileName, ExportFormat::Pdf);
        $pdf->save($paths['absolute_path']);

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
        // 1) Template HTML envoyé par le front
        $rawTemplate = $definition->meta['template_html'] ?? null;
        if (is_string($rawTemplate) && trim($rawTemplate) !== '') {
            return $this->htmlRenderer->render($rawTemplate, $definition, $sheets);
        }

        // 2) Template Blade nommé
        $bladeView = (string) ($definition->meta['template'] ?? 'exports::pdf.default');
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
