<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Enums;

/**
 * Formats d'export pris en charge par le module.
 *
 * - XLSX : Excel multi-feuilles (driver par défaut pour les exports tabulaires)
 * - CSV  : Universel, pas de dépendance externe (fallback robuste)
 * - PDF  : Rendu via template Blade (utile pour les exports « rapports »)
 */
enum ExportFormat: string
{
    case Xlsx = 'xlsx';
    case Csv = 'csv';
    case Pdf = 'pdf';

    public function mimeType(): string
    {
        return match ($this) {
            self::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Csv => 'text/csv',
            self::Pdf => 'application/pdf',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }

    public static function fromLoose(string $value): self
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'xls', 'xlsx', 'excel', 'spreadsheet' => self::Xlsx,
            'csv' => self::Csv,
            'pdf' => self::Pdf,
            default => self::tryFrom($normalized) ?? self::Xlsx,
        };
    }
}
