<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Drivers;

use App\Core\Domain\Shared\Export\Contracts\ExportDriverInterface;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use App\Core\Domain\Shared\Export\Exceptions\MissingDependencyException;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Driver XLSX (Excel) basé sur phpoffice/phpspreadsheet.
 *
 * Si le paquet n'est pas installé, une exception explicite est levée
 * avec la commande Composer à exécuter.
 *
 *  - Une feuille par ExportSheetDto (le nom Excel est tronqué à 31 caractères)
 *  - En-têtes sur la 1re ligne (gras, fond gris)
 *  - Auto-size des colonnes (limité aux 200 premières colonnes)
 *  - Écriture cellule par cellule pour ne pas charger toute la collection en mémoire
 */
final class XlsxExportDriver implements ExportDriverInterface
{
    public function __construct(private readonly FilenameBuilder $filenames) {}

    public function format(): ExportFormat
    {
        return ExportFormat::Xlsx;
    }

    public function supports(ExportFormat $format): bool
    {
        return $format === ExportFormat::Xlsx;
    }

    public function export(ExportDefinitionDto $definition, iterable $resolvedSheets): ExportResultDto
    {
        if (! class_exists(Spreadsheet::class)) {
            throw MissingDependencyException::forPackage('xlsx', 'phpoffice/phpspreadsheet');
        }

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $index = 0;
        $usedNames = [];

        foreach ($resolvedSheets as $resolved) {
            $worksheet = $spreadsheet->createSheet($index);
            $worksheet->setTitle($this->safeSheetName($resolved->sheet->name, $usedNames));
            $usedNames[] = $worksheet->getTitle();

            // En-têtes
            $headers = $resolved->headers();
            $col = 1;
            foreach ($headers as $header) {
                $worksheet->setCellValue([$col, 1], $header);
                $col++;
            }

            $lastHeaderColLetter = Coordinate::stringFromColumnIndex(max(1, count($headers)));
            $headerRange = 'A1:'.$lastHeaderColLetter.'1';
            $headerStyle = $worksheet->getStyle($headerRange);
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE5E7EB');
            $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->freezePane('A2');

            // Données
            $row = 2;
            $keys = $resolved->fieldKeys();
            foreach ($resolved->rows() as $dataRow) {
                $col = 1;
                foreach ($keys as $k) {
                    $value = $dataRow[$k] ?? null;
                    if (is_array($value)) {
                        $value = (string) json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                    $worksheet->setCellValue([$col, $row], $value);
                    $col++;
                }
                $row++;
            }

            // Auto-size (jusqu'à 200 colonnes pour rester raisonnable)
            for ($c = 1; $c <= min(200, count($headers)); $c++) {
                $worksheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }

            $index++;
        }

        // Si aucune feuille n'a été ajoutée, on crée une feuille vide pour avoir un fichier valide.
        if ($spreadsheet->getSheetCount() === 0) {
            $spreadsheet->createSheet(0)->setTitle('Sheet1');
        }

        $spreadsheet->setActiveSheetIndex(0);

        $paths = $this->filenames->build($definition->fileName, ExportFormat::Xlsx);
        $writer = new XlsxWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($paths['absolute_path']);

        return new ExportResultDto(
            absolutePath: $paths['absolute_path'],
            downloadFileName: $paths['download_name'],
            mimeType: ExportFormat::Xlsx->mimeType(),
        );
    }

    /**
     * @param  array<int, string>  $existing
     */
    private function safeSheetName(string $name, array $existing): string
    {
        $name = preg_replace('/[\\\\\\/?\\*\\[\\]:]+/u', ' ', $name) ?? 'Sheet';
        $name = trim($name);
        $name = $name !== '' ? $name : 'Sheet';
        $name = mb_substr($name, 0, 31);

        $base = $name;
        $i = 2;
        while (in_array($name, $existing, true)) {
            $suffix = ' ('.$i.')';
            $name = mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix;
            $i++;
        }

        return $name;
    }
}
