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
            $path = "Users!A{$line}";
            $email = AdminUserImportRowParser::normalizeEmail($row['email'] ?? null);

            if ($email === '' && $this->rowIsEmpty($row)) {
                continue;
            }

            if ($email === '') {
                $issues[] = new AdminUserImportIssue($path, 'email', __('Email obligatoire.'));

                continue;
            }

            $firstName = AdminUserImportRowParser::nullableString($row['first_name'] ?? null);
            $lastName = AdminUserImportRowParser::nullableString($row['last_name'] ?? null);
            $name = AdminUserImportRowParser::nullableString($row['name'] ?? null)
                ?? AdminUserImportRowParser::nullableString($row['full_name'] ?? null)
                ?? AdminUserImportRowParser::nullableString($row['nom_complet'] ?? null);

            // Complète first/last depuis name, puis depuis un prénom/nom multi-mots.
            [$firstName, $lastName] = AdminUserImportRowParser::resolveFirstAndLastName(
                $firstName,
                $lastName,
                $name,
            );

            $countryCode = AdminUserImportRowParser::nullableString($row['nationality_country_code'] ?? null);
            if ($countryCode !== null) {
                $countryCode = strtoupper($countryCode);
            }

            $rawGender = AdminUserImportRowParser::nullableString($row['gender'] ?? null);
            $gender = AdminUserImportRowParser::normalizeGender($rawGender);
            if ($rawGender !== null && $gender === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'gender',
                    __('Genre non reconnu « :value » (attendu M/F).', ['value' => $rawGender]),
                    'warning',
                );
            }

            $rawCivility = AdminUserImportRowParser::nullableString($row['civility'] ?? null);
            $civility = AdminUserImportRowParser::normalizeCivility($rawCivility);
            if ($rawCivility !== null && $civility === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'civility',
                    __('Civilité non reconnue « :value ».', ['value' => $rawCivility]),
                    'warning',
                );
            }

            $rawMarital = AdminUserImportRowParser::nullableString($row['marital_status'] ?? null);
            $maritalStatus = AdminUserImportRowParser::normalizeMaritalStatus($rawMarital);
            if ($rawMarital !== null && $maritalStatus === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'marital_status',
                    __('Situation maritale non reconnue « :value ».', ['value' => $rawMarital]),
                    'warning',
                );
            }

            $rawCareer = AdminUserImportRowParser::nullableString($row['career_intent'] ?? null);
            $careerIntent = AdminUserImportRowParser::normalizeCareerIntent($rawCareer);
            if ($rawCareer !== null && $careerIntent === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'career_intent',
                    __('Intention de carrière non reconnue « :value ».', ['value' => $rawCareer]),
                    'warning',
                );
            }

            $rawProfileType = AdminUserImportRowParser::nullableString($row['profile_type'] ?? null);
            $profileType = AdminUserImportRowParser::normalizeProfileType($rawProfileType);
            if ($rawProfileType !== null && $profileType === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'profile_type',
                    __('Type de profil non reconnu « :value ».', ['value' => $rawProfileType]),
                    'warning',
                );
            }

            $dateOfBirth = AdminUserImportRowParser::nullableDate($row['date_of_birth'] ?? null);
            $rawDate = AdminUserImportRowParser::nullableString($row['date_of_birth'] ?? null);
            if ($rawDate !== null && $dateOfBirth === null && ! is_numeric($row['date_of_birth'] ?? null)) {
                $issues[] = new AdminUserImportIssue(
                    "Users!G{$line}",
                    'date_of_birth',
                    __('Date de naissance invalide (attendu AAAA-MM-JJ).'),
                );
            }

            $rawPhone = AdminUserImportRowParser::nullableString($row['phone_number1'] ?? null)
                ?? AdminUserImportRowParser::nullableString($row['phone'] ?? null)
                ?? AdminUserImportRowParser::nullableString($row['telephone'] ?? null);

            [$phoneNumber1, $phoneNumber2, $phoneNumber3] = AdminUserImportRowParser::splitAndNormalizePhones(
                $rawPhone,
                $countryCode ?? 'CM',
            );

            if ($rawPhone !== null && $phoneNumber1 === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'phone_number1',
                    __('Téléphone invalide « :value ».', ['value' => $rawPhone]),
                );

                continue;
            }

            if ($phoneNumber2 !== null || $phoneNumber3 !== null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'phone_number1',
                    __('Plusieurs numéros détectés : le 1er va dans phone_number1, les suivants dans phone_number2/3.'),
                    'warning',
                );
            }

            $rawActive = $row['active'] ?? null;
            $active = AdminUserImportRowParser::nullableBool($rawActive);
            if ($rawActive !== null && $rawActive !== '' && $active === null) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'active',
                    __('Valeur « active » non reconnue — défaut : actif.'),
                    'warning',
                );
            }

            // Nom affiché : colonne name, sinon first + last.
            if ($name === null && ($firstName !== null || $lastName !== null)) {
                $composed = trim(implode(' ', array_filter([$firstName, $lastName])));
                $name = $composed !== '' ? $composed : null;
            }

            $rows[] = new AdminUserImportRowData(
                line: $line,
                email: $email,
                firstName: $firstName,
                lastName: $lastName,
                name: $name,
                phoneNumber1: $phoneNumber1,
                phoneNumber2: $phoneNumber2,
                phoneNumber3: $phoneNumber3,
                gender: $gender,
                civility: $civility,
                dateOfBirth: $dateOfBirth,
                placeOfBirth: AdminUserImportRowParser::nullableString($row['place_of_birth'] ?? null),
                nationalityCountryCode: $countryCode,
                residenceCity: AdminUserImportRowParser::nullableString($row['residence_city'] ?? null),
                careerIntent: $careerIntent,
                profileType: $profileType,
                maritalStatus: $maritalStatus,
                numberOfChildren: AdminUserImportRowParser::nullableInt($row['number_of_children'] ?? null),
                roles: AdminUserImportRowParser::roles($row['roles'] ?? null),
                active: $active ?? true,
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
            if ($key === '') {
                continue;
            }
            $key = str_replace([' ', '-'], '_', $key);
            $headers[] = match ($key) {
                'prenom', 'prénom', 'firstname', 'given_name' => 'first_name',
                'nom', 'nom_de_famille', 'lastname', 'family_name', 'surname' => 'last_name',
                'nom_complet', 'fullname_name', 'fullname' => 'name',
                'telephone', 'tel', 'phone', 'mobile' => 'phone_number1',
                'courriel', 'mail', 'e_mail' => 'email',
                default => $key,
            };
        }

        $parsed = [];
        foreach ($rows as $row) {
            $assoc = [];
            $values = array_values($row);
            $isEmpty = true;

            foreach ($headers as $index => $header) {
                $value = $values[$index] ?? null;
                if ($value !== null && preg_replace('/\s+/u', '', trim((string) $value)) !== '') {
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
            if (preg_replace('/\s+/u', '', AdminUserImportRowParser::string($value)) !== '') {
                return false;
            }
        }

        return true;
    }
}
