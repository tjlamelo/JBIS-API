<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import;

use App\Core\Application\Mail\Jobs\DispatchImportedAccountWelcomeMailsJob;
use App\Core\Domain\Identity\Actions\User\CreateAdminUserAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Identity\Enums\ProfileType;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportIssue;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportResult;
use App\Core\Domain\Identity\Import\DTOs\AdminUserImportRowData;
use App\Core\Domain\Identity\Import\Support\AdminUserImportRowParser;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
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

        $emails = array_values(array_unique(array_map(
            static fn (AdminUserImportRowData $row): string => strtolower($row->email),
            $rows,
        )));

        $existingEmails = [];
        if ($emails !== []) {
            $existingEmails = User::query()
                ->where(function ($query) use ($emails): void {
                    foreach ($emails as $email) {
                        $query->orWhereRaw('LOWER(email) = ?', [$email]);
                    }
                })
                ->pluck('email')
                ->map(static fn ($email): string => strtolower((string) $email))
                ->all();
        }

        $existingPhoneFingerprints = $this->loadExistingPhoneFingerprints();

        $emailsInFile = [];
        $phoneFingerprintsInFile = [];
        $validRows = [];
        $skippedRows = [];

        foreach ($rows as $row) {
            $path = "Users!A{$row->line}";
            $email = strtolower($row->email);

            // Email déjà en base → signalé, ignoré à l'import confirmé (pas bloquant).
            if (in_array($email, $existingEmails, true)) {
                $issues[] = new AdminUserImportIssue(
                    $path,
                    'email',
                    __('Email déjà existant — ligne ignorée à l\'import.'),
                    'skip',
                );
                $skippedRows[] = $row;

                continue;
            }

            $rowIssues = $this->validateRow(
                $row,
                $path,
                $countryIdsByCode,
                $emailsInFile,
                $phoneFingerprintsInFile,
                $existingPhoneFingerprints,
            );

            $blocking = array_values(array_filter(
                $rowIssues,
                static fn (AdminUserImportIssue $issue): bool => $issue->severity === 'error',
            ));
            $nonBlocking = array_values(array_filter(
                $rowIssues,
                static fn (AdminUserImportIssue $issue): bool => $issue->severity !== 'error',
            ));

            if ($nonBlocking !== []) {
                $issues = array_merge($issues, $nonBlocking);
            }

            if ($blocking !== []) {
                $issues = array_merge($issues, $blocking);

                continue;
            }

            $emailsInFile[] = $email;
            foreach ([$row->phoneNumber1, $row->phoneNumber2, $row->phoneNumber3] as $phone) {
                if ($phone === null) {
                    continue;
                }
                $fingerprint = AdminUserImportRowParser::phoneFingerprint(
                    $phone,
                    $row->nationalityCountryCode ?? 'CM',
                );
                if ($fingerprint !== null) {
                    $phoneFingerprintsInFile[] = $fingerprint;
                }
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
                skippedCount: count($skippedRows),
            );
        }

        $createdIds = [];
        /** @var list<array{user_id: int, plain_password: string}> $pendingMails */
        $pendingMails = [];

        DB::transaction(function () use ($validRows, $countryIdsByCode, &$createdIds, &$pendingMails): void {
            foreach ($validRows as $row) {
                $countryId = null;
                if ($row->nationalityCountryCode !== null) {
                    $countryId = $countryIdsByCode[strtoupper($row->nationalityCountryCode)] ?? null;
                }

                $payload = $row->toWriteArray($countryId);
                $payload['send_account_email'] = false;

                $created = $this->createAdminUser->execute(AdminUserWriteDto::fromArray($payload));
                $user = $created['user'];
                $createdIds[] = (int) $user->id;

                if ($user->canReceiveEmail()) {
                    $user->forceFill([
                        'must_change_password' => true,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ])->save();

                    $pendingMails[] = [
                        'user_id' => (int) $user->id,
                        'plain_password' => $created['plain_password'],
                    ];
                }
            }
        });

        if ($pendingMails !== []) {
            DispatchImportedAccountWelcomeMailsJob::dispatch($pendingMails);
        }

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
            emailsQueued: count($pendingMails),
            skippedCount: count($skippedRows),
        );
    }

    /**
     * @return list<string>
     */
    private function loadExistingPhoneFingerprints(): array
    {
        $phones = User::query()
            ->whereNotNull('phone_number1')
            ->where('phone_number1', '!=', '')
            ->pluck('phone_number1')
            ->all();

        $profilePhones = UserProfile::query()
            ->where(function ($query): void {
                $query->whereNotNull('phone_number2')->where('phone_number2', '!=', '')
                    ->orWhere(function ($q): void {
                        $q->whereNotNull('phone_number3')->where('phone_number3', '!=', '');
                    });
            })
            ->get(['phone_number2', 'phone_number3']);

        foreach ($profilePhones as $profile) {
            if (filled($profile->phone_number2)) {
                $phones[] = (string) $profile->phone_number2;
            }
            if (filled($profile->phone_number3)) {
                $phones[] = (string) $profile->phone_number3;
            }
        }

        return collect($phones)
            ->map(static fn ($phone): ?string => AdminUserImportRowParser::phoneFingerprint((string) $phone, 'CM'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $countryIdsByCode
     * @param  list<string>  $emailsInFile
     * @param  list<string>  $phoneFingerprintsInFile
     * @param  list<string>  $existingPhoneFingerprints
     * @return list<AdminUserImportIssue>
     */
    private function validateRow(
        AdminUserImportRowData $row,
        string $path,
        array $countryIdsByCode,
        array $emailsInFile,
        array $phoneFingerprintsInFile,
        array $existingPhoneFingerprints,
    ): array {
        $issues = [];
        $email = strtolower($row->email);

        if ($row->firstName === null || $row->lastName === null) {
            if ($row->firstName === null) {
                $issues[] = new AdminUserImportIssue($path, 'first_name', __('Prénom obligatoire.'));
            }
            if ($row->lastName === null) {
                $issues[] = new AdminUserImportIssue($path, 'last_name', __('Nom obligatoire.'));
            }
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $issues[] = new AdminUserImportIssue($path, 'email', __('Email invalide.'));
        }

        if (in_array($email, $emailsInFile, true)) {
            $issues[] = new AdminUserImportIssue($path, 'email', __('Email en doublon dans le fichier.'));
        }

        $rowPhones = [
            'phone_number1' => $row->phoneNumber1,
            'phone_number2' => $row->phoneNumber2,
            'phone_number3' => $row->phoneNumber3,
        ];
        $seenInRow = [];

        foreach ($rowPhones as $field => $phone) {
            if ($phone === null) {
                continue;
            }

            $fingerprint = AdminUserImportRowParser::phoneFingerprint(
                $phone,
                $row->nationalityCountryCode ?? 'CM',
            );

            if ($fingerprint === null) {
                $issues[] = new AdminUserImportIssue($path, $field, __('Téléphone invalide.'));

                continue;
            }

            if (isset($seenInRow[$fingerprint])) {
                $issues[] = new AdminUserImportIssue($path, $field, __('Téléphone en doublon sur la même ligne.'));

                continue;
            }
            $seenInRow[$fingerprint] = true;

            if (in_array($fingerprint, $existingPhoneFingerprints, true)) {
                $issues[] = new AdminUserImportIssue($path, $field, __('Ce téléphone existe déjà.'));
            }
            if (in_array($fingerprint, $phoneFingerprintsInFile, true)) {
                $issues[] = new AdminUserImportIssue($path, $field, __('Téléphone en doublon dans le fichier.'));
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
            'phone_number2' => ['nullable', 'string', 'max:20'],
            'phone_number3' => ['nullable', 'string', 'max:20'],
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
