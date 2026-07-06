<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ProcessFlowImportTemplateGenerator
{
    public function __construct(
        private readonly ProcessFlowImportTemplateCatalog $catalog,
    ) {}

    public function generate(string $destinationPath): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $this->addInstructionsSheet($spreadsheet);
        $this->addFlowsSheet($spreadsheet);
        $this->addSectionsSheet($spreadsheet);
        $this->addStepsSheet($spreadsheet);
        $this->addStepDocumentsSheet($spreadsheet);
        $this->addDocumentTypesCatalogSheet($spreadsheet);
        $this->addCountriesCatalogSheet($spreadsheet);
        $this->addEnumsCatalogSheet($spreadsheet);
        $this->addSectionKeysCatalogSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $dir = dirname($destinationPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        (new Xlsx($spreadsheet))->save($destinationPath);

        return $destinationPath;
    }

    public function defaultPath(): string
    {
        return storage_path('app/templates/process-flow-import-template.xlsx');
    }

    private function addInstructionsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(0);
        $sheet->setTitle('Instructions');

        $lines = [
            ['Règle', 'Détail'],
            ['Champs bilingues (JSON en base)', 'Flows : name_fr + name_en · Sections : title_fr + title_en · Steps : title_fr + title_en (+ description_fr/en optionnel)'],
            ['country_code', 'Code ISO exact — voir feuille _Countries (ex. DE, FR, CM)'],
            ['document_type_code', 'Code exact du catalogue — voir feuille _DocumentTypes (ex. PASSPORT, CV). N\'inventez pas de code (ex. GERMAN_B1_CERTIFICATE).'],
            ['section_key', 'Clé technique stable — voir feuille _SectionKeys ou slug depuis title_fr'],
            ['step_type / payment_type / responsible_party', 'Valeurs autorisées — voir feuille _Enums'],
            ['accepted_banks', 'Liste séparée par des virgules — codes de la feuille _Enums (type accepted_bank)'],
            ['file_opening_fee', 'LAISSEZ VIDE pour calcul auto = somme des étapes PAYMENT + payment_type FILE_OPENING'],
            ['total_procedure_fees', 'LAISSEZ VIDE pour calcul auto = somme des étapes PAYMENT hors FILE_OPENING'],
            ['estimated_duration_days', 'LAISSEZ VIDE pour calcul auto = somme des estimated_duration_days des étapes'],
            ['step_order (StepDocuments)', 'Numéro global de l\'étape (1, 2, 3…) dans l\'ordre des sections, pas le numéro au sein de la section'],
            ['Champs interdits', 'id, status, flow_group_id, version, document_type_ids, country_id, program_id, offer_id…'],
            ['Lignes de commentaire', 'Les lignes dont la première cellule commence par # sont ignorées à l\'import'],
        ];

        $sheet->fromArray($lines);
        $this->styleHeaderRow($sheet, 1, 2);
        $sheet->getColumnDimension('A')->setWidth(42);
        $sheet->getColumnDimension('B')->setWidth(90);
    }

    private function addFlowsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(1);
        $sheet->setTitle('Flows');
        $sheet->fromArray([
            ['flow_key', 'country_code', 'name_fr', 'name_en', 'file_opening_fee', 'total_procedure_fees', 'estimated_duration_days'],
            ['allemagne', 'DE', 'Infirmier - Allemagne', 'Nurse - Germany', '', '', ''],
        ]);
        $this->styleHeaderRow($sheet, 1, 7);
    }

    private function addSectionsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(2);
        $sheet->setTitle('Sections');
        $sheet->fromArray([
            ['flow_key', 'section_key', 'title_fr', 'title_en', 'order'],
            ['allemagne', 'ouverture', 'Ouverture de dossier', 'File opening', 1],
        ]);
        $this->styleHeaderRow($sheet, 1, 5);
    }

    private function addStepsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(3);
        $sheet->setTitle('Steps');
        $sheet->fromArray([
            ['flow_key', 'section_key', 'step_order', 'step_type', 'payment_type', 'responsible_party', 'title_fr', 'title_en', 'amount', 'is_blocking', 'is_required', 'accepted_banks', 'estimated_duration_days'],
            ['allemagne', 'ouverture', 1, 'DOCUMENT_COLLECTION', '', 'CANDIDATE', 'Dépôt dossier initial', 'Initial file submission', 0, 'true', 'true', '', 30],
            ['allemagne', 'ouverture', 2, 'PAYMENT', 'FILE_OPENING', 'CANDIDATE', 'Frais d\'ouverture', 'File opening fee', 350000, 'true', 'true', 'ORIS_FINANCE,SCB', ''],
            ['allemagne', 'ouverture', 3, 'PAYMENT', 'PROCEDURE_INSTALMENT', 'CANDIDATE', '1er versement procédure', '1st procedure instalment', 150000, 'true', 'true', 'ORIS_FINANCE,SCB', 60],
        ]);
        $this->styleHeaderRow($sheet, 1, 13);
    }

    private function addStepDocumentsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(4);
        $sheet->setTitle('StepDocuments');
        $sheet->fromArray([
            ['flow_key', 'step_order', 'document_type_code'],
            ['allemagne', 1, 'CV'],
            ['allemagne', 1, 'PASSPORT'],
            ['allemagne', 1, 'DIPLOMA'],
        ]);
        $this->styleHeaderRow($sheet, 1, 3);
    }

    private function addDocumentTypesCatalogSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(5);
        $sheet->setTitle('_DocumentTypes');

        $rows = [['document_type_code', 'label_fr', 'label_en']];
        foreach ($this->catalog->documentTypes() as $type) {
            $rows[] = [$type['code'], $type['label_fr'], $type['label_en']];
        }

        $sheet->fromArray($rows);
        $this->styleHeaderRow($sheet, 1, 3);
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(36);
        $sheet->getColumnDimension('C')->setWidth(36);
    }

    private function addCountriesCatalogSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(6);
        $sheet->setTitle('_Countries');

        $rows = [['country_code', 'name_fr', 'name_en']];
        foreach ($this->catalog->countries() as $country) {
            $rows[] = [$country['code'], $country['name_fr'], $country['name_en']];
        }

        $sheet->fromArray($rows);
        $this->styleHeaderRow($sheet, 1, 3);
    }

    private function addEnumsCatalogSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(7);
        $sheet->setTitle('_Enums');

        $rows = [['field', 'code', 'label_fr']];
        foreach ($this->catalog->enums() as $enum) {
            $rows[] = [$enum['type'], $enum['code'], $enum['label_fr']];
        }

        $sheet->fromArray($rows);
        $this->styleHeaderRow($sheet, 1, 3);
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(48);
    }

    private function addSectionKeysCatalogSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(8);
        $sheet->setTitle('_SectionKeys');

        $rows = [['section_key', 'title_fr', 'title_en']];
        foreach ($this->catalog->sectionKeys() as $sectionKey) {
            $rows[] = [$sectionKey['key'], $sectionKey['title_fr'], $sectionKey['title_en']];
        }

        $sheet->fromArray($rows);
        $this->styleHeaderRow($sheet, 1, 3);
    }

    private function styleHeaderRow(Worksheet $sheet, int $row, int $columnCount): void
    {
        $start = Coordinate::stringFromColumnIndex(1).$row;
        $end = Coordinate::stringFromColumnIndex($columnCount).$row;
        $range = "{$start}:{$end}";

        $sheet->getStyle($range)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF01233C');

        $sheet->getStyle($range)
            ->getFont()
            ->getColor()
            ->setARGB('FFFFFFFF');
    }
}
