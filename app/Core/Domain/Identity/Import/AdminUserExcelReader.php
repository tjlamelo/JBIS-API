<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import;

use App\Core\Domain\Identity\Import\DTOs\AdminUserImportIssue;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportRowData;
use App\Core\Domain\Identity\Import\Support\AdminUserImportRowParser;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class AdminUserExcelReader
{
    /**
     * @return array{rows: list<AdminUserImportRowData>, issues: list<AdminUserImportIssue>}
     */
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $this->resolveSheet($spreadsheet);
        if ($sheet === null) {
            return [
                'rows' => [],
                'issues' => [
                    new AdminUserImportIssue(
                        'Users',
                        'sheet',
                        __('La feuille « Users » est introuvable.'),
                    ),
                ],
            ];
        }

        $rawRows = $this->rowsFromSheet($sheet);
        $rows = [];
        $issues = [];

        foreach ($rawRows as $rowIndex => $row) {
            $line = $rowIndex + 2;
            $email = strtolower(AdminUserImportRowParser::string($row['email'] ?? null));

            if ($email === '' && $this->rowIsEmpty($row)) {
                continue;
            }

            if ($email === '') {
                $issues[] = new AdminUserImportIssue("Users!A{$line}", 'email', __('Email obligatoire.'));

                continue;
            }

            $firstName = AdminUserImportRowParser::nullableString($row['first_name'] ?? null);
            $lastName = AdminUserImportRowParser::nullableString($row['last_name'] ?? null);
            $name = AdminUserImportRowParser::nullableString($row['name'] ?? null);
            $gender = AdminUserImportRowParser::normalizeGender(
                AdminUserImportRowParser::nullableString($row['gender'] ?? null),
            );
            $civility = AdminUserImportRowParser::normalizeCivility(
                AdminUserImportRowParser::nullableString($row['civility'] ?? null),
            );
            $dateOfBirth = AdminUserImportRowParser::nullableDate($row['date_of_birth'] ?? null);
            $rawDate = AdminUserImportRowParser::nullableString($row['date_of_birth'] ?? null);
            if ($rawDate !== null && $dateOfBirth === null && ! is_numeric($row['date_of_birth'] ?? null)) {
                $issues[] = new AdminUserImportIssue(
                    "Users!G{$line}",
                    'date_of_birth',
                    __('Date de naissance invalide (attendu AAAA-MM-JJ).'),
                );
            }

            $rows[] = new AdminUserImportRowData(
                line: $line,
                email: $email,
                firstName: $firstName,
                lastName: $lastName,
                name: $name,
                phoneNumber1: AdminUserImportRowParser::nullableString($row['phone_number1'] ?? null),
                gender: $gender,
                civility: $civility,
                dateOfBirth: $dateOfBirth,
                placeOfBirth: AdminUserImportRowParser::nullableString($row['place_of_birth'] ?? null),
                nationalityCountryCode: AdminUserImportRowParser::nullableString(
                    $row['nationality_country_code'] ?? null,
                ),
                residenceCity: AdminUserImportRowParser::nullableString($row['residence_city'] ?? null),
                careerIntent: AdminUserImportRowParser::nullableString($row['career_intent'] ?? null),
                profileType: AdminUserImportRowParser::nullableString($row['profile_type'] ?? null),
                maritalStatus: AdminUserImportRowParser::normalizeMaritalStatus(
                    AdminUserImportRowParser::nullableString($row['marital_status'] ?? null),
                ),
                numberOfChildren: AdminUserImportRowParser::nullableInt($row['number_of_children'] ?? null),
                roles: AdminUserImportRowParser::roles($row['roles'] ?? null),
                active: AdminUserImportRowParser::nullableBool($row['active'] ?? null),
                password: AdminUserImportRowParser::nullableString($row['password'] ?? null),
            );
        }

        return ['rows' => $rows, 'issues' => $issues];
    }

    private function resolveSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): ?Worksheet
    {
        foreach (['Users', 'users', 'Utilisateurs', 'Candidats'] as $title) {
            $sheet = $spreadsheet->getSheetByName($title);
            if ($sheet !== null) {
                return $sheet;
            }
        }

        return $spreadsheet->getSheetCount() > 0 ? $spreadsheet->getSheet(0) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rowsFromSheet(Worksheet $sheet): array
    {
        $rows = $sheet->toArray(null, true, true, true);
        if ($rows === []) {
            return [];
        }

        $headerRow = array_shift($rows);
        $headers = [];
        foreach ($headerRow as $cell) {
            $key = strtolower(trim((string) $cell));
            if ($key !== '') {
                $headers[] = str_replace(' ', '_', $key);
            }
        }

        $parsed = [];
        foreach ($rows as $row) {
            $assoc = [];
            $values = array_values($row);
            $isEmpty = true;

            foreach ($headers as $index => $header) {
                $value = $values[$index] ?? null;
                if ($value !== null && trim((string) $value) !== '') {
                    $isEmpty = false;
                }
                $assoc[$header] = $value;
            }

            if (! $isEmpty) {
                $firstCell = trim((string) ($values[0] ?? ''));
                if ($firstCell !== '' && str_starts_with($firstCell, '#')) {
                    continue;
                }

                $parsed[] = $assoc;
            }
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (AdminUserImportRowParser::string($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
