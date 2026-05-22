<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Drivers;

use App\Core\Domain\Shared\Export\Contracts\ExportDriverInterface;
use App\Core\Domain\Shared\Export\Contracts\ResolvedSheet;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use ZipArchive;

/**
 * Driver CSV — toujours disponible (aucune dépendance externe).
 *
 *  - Si l'export n'a qu'une feuille → un fichier .csv (UTF-8 + BOM)
 *  - Sinon → archive .zip contenant un .csv par feuille
 */
final class CsvExportDriver implements ExportDriverInterface
{
    public function __construct(private readonly FilenameBuilder $filenames) {}

    public function format(): ExportFormat
    {
        return ExportFormat::Csv;
    }

    public function supports(ExportFormat $format): bool
    {
        return $format === ExportFormat::Csv;
    }

    public function export(ExportDefinitionDto $definition, iterable $resolvedSheets): ExportResultDto
    {
        $sheets = is_array($resolvedSheets) ? $resolvedSheets : iterator_to_array($resolvedSheets);

        if (count($sheets) <= 1) {
            return $this->exportSingleCsv($definition, $sheets[0] ?? null);
        }

        return $this->exportZippedCsv($definition, $sheets);
    }

    private function exportSingleCsv(ExportDefinitionDto $definition, ?ResolvedSheet $sheet): ExportResultDto
    {
        $paths = $this->filenames->build($definition->fileName, ExportFormat::Csv);
        $absolute = $paths['absolute_path'];

        $handle = fopen($absolute, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier CSV en écriture : {$absolute}");
        }

        try {
            // BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");

            if ($sheet !== null) {
                $this->writeSheetTo($handle, $sheet);
            }
        } finally {
            fclose($handle);
        }

        return new ExportResultDto(
            absolutePath: $absolute,
            downloadFileName: $paths['download_name'],
            mimeType: ExportFormat::Csv->mimeType(),
        );
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     */
    private function exportZippedCsv(ExportDefinitionDto $definition, array $sheets): ExportResultDto
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException("L'extension PHP « zip » est requise pour exporter plusieurs feuilles en CSV.");
        }

        $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'export-csv-'.uniqid();
        @mkdir($tmpDir, 0775, true);

        $csvPaths = [];
        try {
            foreach ($sheets as $i => $sheet) {
                $safeName = preg_replace('/[^a-z0-9_-]+/i', '_', $sheet->sheet->name) ?: ('sheet-'.($i + 1));
                $csvPath = $tmpDir.DIRECTORY_SEPARATOR.$safeName.'.csv';
                $h = fopen($csvPath, 'wb');
                if ($h === false) {
                    throw new \RuntimeException("Impossible d'écrire le CSV temporaire : {$csvPath}");
                }
                fwrite($h, "\xEF\xBB\xBF");
                $this->writeSheetTo($h, $sheet);
                fclose($h);
                $csvPaths[] = ['path' => $csvPath, 'name' => $safeName.'.csv'];
            }

            $paths = $this->filenames->build($definition->fileName, ExportFormat::Csv);
            // Forcer une extension .zip pour le téléchargement multi-feuilles
            $absolute = preg_replace('/\.csv$/i', '.zip', $paths['absolute_path']);
            $downloadName = preg_replace('/\.csv$/i', '.zip', $paths['download_name']);

            $zip = new ZipArchive;
            if ($zip->open($absolute, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Impossible de créer l'archive ZIP : {$absolute}");
            }

            foreach ($csvPaths as $entry) {
                $zip->addFile($entry['path'], $entry['name']);
            }
            $zip->close();

            return new ExportResultDto(
                absolutePath: $absolute,
                downloadFileName: $downloadName,
                mimeType: 'application/zip',
            );
        } finally {
            foreach ($csvPaths as $entry) {
                @unlink($entry['path']);
            }
            @rmdir($tmpDir);
        }
    }

    /**
     * @param  resource  $handle
     */
    private function writeSheetTo($handle, ResolvedSheet $sheet): void
    {
        fputcsv($handle, $sheet->headers(), ',', '"', '\\');

        $keys = $sheet->fieldKeys();
        foreach ($sheet->rows() as $row) {
            $ordered = [];
            foreach ($keys as $k) {
                $value = $row[$k] ?? null;
                $ordered[] = $value === null ? '' : (is_scalar($value) ? (string) $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE));
            }
            fputcsv($handle, $ordered, ',', '"', '\\');
        }
    }
}
