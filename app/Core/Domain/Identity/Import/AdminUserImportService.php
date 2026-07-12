<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import;

use App\Core\Domain\Identity\Actions\User\CreateAdminUserAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Identity\Enums\ProfileType;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportIssue;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportResult;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportRowData;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Location\Models\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class AdminUserImportService
{
    public function __construct(
        private readonly AdminUserExcelReader $reader,
        private readonly CreateAdminUserAction $createAdminUser,
    ) {}

    public function import(string $filePath, bool $commit): AdminUserImportResult
    {
        $parsed = $this->reader->read($filePath);
        $issues = $parsed['issues'];
        /** @var list<AdminUserImportRowData> $rows */
        $rows = $parsed['rows'];

        if ($rows === [] && $issues === []) {
            $issues[] = new AdminUserImportIssue('Users', 'rows', __('Aucune ligne utilisateur à importer.'));
        }

        $countryIdsByCode = Country::query()
            ->where('is_active', true)
            ->pluck('id', 'code')
            ->mapWithKeys(static fn ($id, $code): array => [strtoupper((string) $code) => (int) $id])
            ->all();

        $existingEmails = User::query()
            ->whereIn('email', array_map(static fn (AdminUserImportRowData $row): string => $row->email, $rows))
            ->pluck('email')
            ->map(static fn ($email): string => strtolower((string) $email))
            ->all();

        $existingPhones = User::query()
            ->whereNotNull('phone_number1')
            ->whereIn(
                'phone_number1',
                array_values(array_filter(array_map(
                    static fn (AdminUserImportRowData $row): ?string => $row->phoneNumber1,
                    $rows,
                ))),
            )
            ->pluck('phone_number1')
            ->map(static fn ($phone): string => (string) $phone)
            ->all();

        $emailsInFile = [];
        $phonesInFile = [];
        $validRows = [];

        foreach ($rows as $row) {
            $path = "Users!A{$row->line}";
            $rowIssues = $this->validateRow(
                $row,
                $path,
                $countryIdsByCode,
                $existingEmails,
                $existingPhones,
                $emailsInFile,
                $phonesInFile,
            );

            if ($rowIssues !== []) {
                $issues = array_merge($issues, $rowIssues);

                continue;
            }

            $emailsInFile[] = $row->email;
            if ($row->phoneNumber1 !== null) {
                $phonesInFile[] = $row->phoneNumber1;
            }

            $validRows[] = $row;
        }

        $hasErrors = $this->hasErrors($issues);
        if ($hasErrors || ! $commit) {
            return new AdminUserImportResult(
                success: ! $hasErrors,
                committed: false,
                validRows: count($validRows),
                createdCount: 0,
                issues: $issues,
                rows: array_map(
                    static fn (AdminUserImportRowData $row): array => [
                        'line' => $row->line,
                        'email' => $row->email,
                    ],
                    $validRows,
                ),
            );
        }

        $createdIds = [];

        DB::transaction(function () use ($validRows, $countryIdsByCode, &$createdIds): void {
            foreach ($validRows as $row) {
                $countryId = null;
                if ($row->nationalityCountryCode !== null) {
                    $countryId = $countryIdsByCode[strtoupper($row->nationalityCountryCode)] ?? null;
                }

                    $created = $this->createAdminUser->execute(
                        AdminUserWriteDto::fromArray($row->toWriteArray($countryId)),
                    );
                    $user = $created['user'];
                $createdIds[] = (int) $user->id;
            }
        });

        return new AdminUserImportResult(
            success: true,
            committed: true,
            validRows: count($validRows),
            createdCount: count($createdIds),
            issues: $issues,
            rows: array_map(
                static fn (AdminUserImportRowData $row): array => [
                    'line' => $row->line,
                    'email' => $row->email,
                ],
                $validRows,
            ),
            createdUserIds: $createdIds,
        );
    }

    /**
     * @param  array<string, int>  $countryIdsByCode
     * @param  list<string>  $existingEmails
     * @param  list<string>  $existingPhones
     * @param  list<string>  $emailsInFile
     * @param  list<string>  $phonesInFile
     * @return list<AdminUserImportIssue>
     */
    private function validateRow(
        AdminUserImportRowData $row,
        string $path,
        array $countryIdsByCode,
        array $existingEmails,
        array $existingPhones,
        array $emailsInFile,
        array $phonesInFile,
    ): array {
        $issues = [];

        if ($row->firstName === null || $row->lastName === null) {
            if ($row->firstName === null) {
                $issues[] = new AdminUserImportIssue($path, 'first_name', __('Prénom obligatoire.'));
            }
            if ($row->lastName === null) {
                $issues[] = new AdminUserImportIssue($path, 'last_name', __('Nom obligatoire.'));
            }
        }

        if (! filter_var($row->email, FILTER_VALIDATE_EMAIL)) {
            $issues[] = new AdminUserImportIssue($path, 'email', __('Email invalide.'));
        }

        if (in_array($row->email, $existingEmails, true)) {
            $issues[] = new AdminUserImportIssue($path, 'email', __('Cet email existe déjà.'));
        }

        if (in_array($row->email, $emailsInFile, true)) {
            $issues[] = new AdminUserImportIssue($path, 'email', __('Email en doublon dans le fichier.'));
        }

        if ($row->phoneNumber1 !== null) {
            if (in_array($row->phoneNumber1, $existingPhones, true)) {
                $issues[] = new AdminUserImportIssue($path, 'phone_number1', __('Ce téléphone existe déjà.'));
            }
            if (in_array($row->phoneNumber1, $phonesInFile, true)) {
                $issues[] = new AdminUserImportIssue($path, 'phone_number1', __('Téléphone en doublon dans le fichier.'));
            }
        }

        if ($row->gender !== null && ! in_array($row->gender, ['M', 'F'], true)) {
            $issues[] = new AdminUserImportIssue($path, 'gender', __('Genre invalide (M ou F).'));
        }

        if ($row->civility !== null && ! in_array($row->civility, Civility::values(), true)) {
            $issues[] = new AdminUserImportIssue($path, 'civility', __('Civilité invalide.'));
        }

        if ($row->civility !== null && ! Civility::isAllowedForGender($row->civility, $row->gender)) {
            $issues[] = new AdminUserImportIssue($path, 'civility', __('La civilité ne correspond pas au genre.'));
        }

        if ($row->dateOfBirth !== null) {
            try {
                $dob = Carbon::parse($row->dateOfBirth)->startOfDay();
                $min = now()->subYears(18)->startOfDay();
                $max = Carbon::parse('1940-01-01')->startOfDay();
                if ($dob->gt($min) || $dob->lt($max)) {
                    $issues[] = new AdminUserImportIssue(
                        $path,
                        'date_of_birth',
                        __('Date de naissance hors plage (18 ans minimum, après 1940).'),
                    );
                }
            } catch (\Throwable) {
                $issues[] = new AdminUserImportIssue($path, 'date_of_birth', __('Date de naissance invalide.'));
            }
        }

        if ($row->nationalityCountryCode !== null) {
            $code = strtoupper($row->nationalityCountryCode);
            if (! isset($countryIdsByCode[$code])) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'nationality_country_code',
                    __('Code pays inconnu : :code', ['code' => $code]),
                );
            }
        }

        if ($row->careerIntent !== null && ! in_array($row->careerIntent, CareerIntent::values(), true)) {
            $issues[] = new AdminUserImportIssue($path, 'career_intent', __('Intention de carrière invalide.'));
        }

        if ($row->profileType !== null && ! in_array($row->profileType, ProfileType::values(), true)) {
            $issues[] = new AdminUserImportIssue($path, 'profile_type', __('Type de profil invalide.'));
        }

        if ($row->maritalStatus !== null && ! in_array($row->maritalStatus, ['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'], true)) {
            $issues[] = new AdminUserImportIssue($path, 'marital_status', __('Situation maritale invalide.'));
        }

        if ($row->numberOfChildren !== null && ($row->numberOfChildren < 0 || $row->numberOfChildren > 20)) {
            $issues[] = new AdminUserImportIssue($path, 'number_of_children', __('Nombre d’enfants invalide.'));
        }

        $roles = $row->roles !== [] ? $row->roles : [ApplicationRole::CANDIDATE];
        foreach ($roles as $role) {
            if (! in_array($role, ApplicationRole::ALL, true)) {
                $issues[] = new AdminUserImportIssue($path, 'roles', __('Rôle inconnu : :role', ['role' => $role]));
            }
        }

        $payload = $row->toWriteArray(
            $row->nationalityCountryCode !== null
                ? ($countryIdsByCode[strtoupper($row->nationalityCountryCode)] ?? null)
                : null,
        );
        $payload['roles'] = $roles;

        $validator = Validator::make($payload, [
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone_number1' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $issues[] = new AdminUserImportIssue($path, (string) $field, (string) $message);
                }
            }
        }

        return $issues;
    }

    /**
     * @param  list<AdminUserImportIssue>  $issues
     */
    private function hasErrors(array $issues): bool
    {
        foreach ($issues as $issue) {
            if ($issue->severity === 'error') {
                return true;
            }
        }

        return false;
    }
}
