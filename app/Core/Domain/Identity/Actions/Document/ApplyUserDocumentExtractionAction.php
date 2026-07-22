<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\Language as UserLanguage;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Identity\Support\CountryNameResolver;
use App\Core\Domain\Identity\Support\Document\CvExtractionSectionFingerprint;
use App\Core\Domain\Identity\Support\GenderNormalizer;
use App\Core\Domain\Identity\Support\LanguageCatalogResolver;
use App\Core\Domain\Identity\Support\MaritalStatusNormalizer;
use App\Core\Domain\Identity\Support\SkillCatalogResolver;
use App\Core\Domain\Location\Models\LanguageLevel;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use App\Core\Domain\Shared\Ai\Support\AiScalarText;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persiste le brouillon validé dans le profil et les tables liées.
 */
final class ApplyUserDocumentExtractionAction
{
    public function __construct(
        private readonly CountryNameResolver $countryResolver,
        private readonly LanguageCatalogResolver $languageResolver,
        private readonly SkillCatalogResolver $skillResolver,
        private readonly MaritalStatusNormalizer $maritalStatusNormalizer,
        private readonly GenderNormalizer $genderNormalizer,
    ) {}

    /**
     * @param  array<string, mixed>|null  $overrides  Corrections manuelles avant application.
     * @return array{profile: UserProfile, document: UserDocument}
     */
    public function execute(UserDocumentExtraction $extraction, User $reviewer, ?array $overrides = null): array
    {
        if ($extraction->status !== DocumentExtractionStatus::PendingReview) {
            throw new \RuntimeException('Seul un brouillon en attente de validation peut être appliqué.');
        }

        /** @var array<string, mixed> $draft */
        $draft = $overrides ?? $extraction->draft_payload ?? [];
        if ($draft === []) {
            throw new \RuntimeException('Brouillon vide.');
        }

        return DB::transaction(function () use ($extraction, $reviewer, $draft): array {
            $document = UserDocument::query()
                ->with('documentType')
                ->lockForUpdate()
                ->findOrFail($extraction->user_document_id);

            $user = User::query()->findOrFail($extraction->user_id);
            $typeCode = strtoupper((string) ($document->documentType?->code ?? 'CV'));
            $profile = $this->applyUserProfile($user, $draft);
            $this->applyUserDocumentFields($document, $draft);
            $this->applyTypedDocumentSections($user, $document, $draft, $typeCode);

            $extraction->update([
                'status' => DocumentExtractionStatus::Applied,
                'applied_payload' => $draft,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'applied_at' => now(),
            ]);

            return [
                'profile' => $profile->fresh(['nationality']),
                'document' => $document->fresh(['documentType', 'issuingCountry']),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyUserProfile(User $user, array $draft): UserProfile
    {
        $source = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];
        $profile = $user->profile()->firstOrNew(['user_id' => $user->id]);

        $map = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'date_of_birth' => 'date_of_birth',
            'place_of_birth' => 'place_of_birth',
            'residence_city_name' => 'residence_city',
            'address' => 'address',
            'phone_number2' => 'phone_number2',
            'phone_number3' => 'phone_number3',
            'gender' => 'gender',
            'bio' => 'bio',
            'marital_status' => 'marital_status',
            'number_of_children' => 'number_of_children',
            'email_institutional' => 'email_institutional',
        ];

        $attributes = [];
        foreach ($map as $from => $to) {
            $value = $source[$from] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            if ($to === 'marital_status') {
                $value = $this->maritalStatusNormalizer->normalize((string) $value) ?? $value;
            }
            if ($to === 'gender') {
                $value = $this->genderNormalizer->normalize((string) $value) ?? $value;
            }
            $attributes[$to] = $value;
        }

        if (! empty($source['nationality_country_name'])) {
            $countryId = $this->countryResolver->resolveId((string) $source['nationality_country_name']);
            if ($countryId !== null) {
                $attributes['nationality_country_id'] = $countryId;
            }
        }

        if ($attributes !== []) {
            $profile->fill($attributes);
            $profile->user_id = $user->id;
            $profile->save();
        }

        return $profile;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyUserDocumentFields(UserDocument $document, array $draft): void
    {
        $source = is_array($draft['user_document'] ?? null) ? $draft['user_document'] : [];
        $updates = [];

        foreach (['document_number', 'issue_date', 'expiry_date'] as $field) {
            $value = $source[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $updates[$field] = $this->parseDateOrString($value);
            }
        }

        if (! empty($source['issuing_country_name'])) {
            $countryId = $this->countryResolver->resolveId((string) $source['issuing_country_name']);
            if ($countryId !== null) {
                $updates['issuing_country_id'] = $countryId;
            }
        }

        if ($updates !== []) {
            $document->fill($updates);
            $document->save();
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyTypedDocumentSections(User $user, UserDocument $document, array $draft, string $typeCode): void
    {
        match ($typeCode) {
            'CV' => $this->applyCvSections($user, $document, $draft),
            'WORK_CERTIFICATE' => $this->applyExperienceSections($user, $document, $draft),
            'PROFESSIONAL_CERTIFICATION', 'TRAINING_CERTIFICATE' => $this->applyCertificationSections($user, $document, $draft),
            'DIPLOMA', 'TRANSCRIPT', 'SUCCESS_CERTIFICATE' => $this->applyEducationSections($user, $document, $draft),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyEducationSections(User $user, UserDocument $document, array $draft): void
    {
        $existingEducations = $this->existingEducationFingerprints($user->id);

        foreach (is_array($draft['educations'] ?? null) ? $draft['educations'] : [] as $row) {
            if (! is_array($row) || trim((string) ($row['degree'] ?? '')) === '') {
                continue;
            }

            $fingerprint = CvExtractionSectionFingerprint::education($row);
            if ($fingerprint === '' || isset($existingEducations[$fingerprint])) {
                continue;
            }

            // start_date is NOT NULL; CVs often only show graduation year → fall back to end_date.
            $startDate = $this->resolveRequiredStartDate($row);
            if ($startDate === null) {
                continue;
            }

            Education::query()->create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'degree' => (string) $row['degree'],
                'institution_name' => (string) ($row['institution_name'] ?? ''),
                'field_of_study' => (string) ($row['field_of_study'] ?? ''),
                'country_id' => $this->countryResolver->resolveId((string) ($row['country_name'] ?? '')),
                'residence_city' => (string) ($row['city_name'] ?? ''),
                'start_date' => $startDate,
                'end_date' => $this->parseDateOrNull($row['end_date'] ?? null),
                'is_current' => (bool) ($row['is_current'] ?? false),
                'grade' => (string) ($row['grade'] ?? ''),
            ]);
            $existingEducations[$fingerprint] = true;
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyExperienceSections(User $user, UserDocument $document, array $draft): void
    {
        $existingExperiences = $this->existingExperienceFingerprints($user->id);

        foreach (is_array($draft['experiences'] ?? null) ? $draft['experiences'] : [] as $row) {
            if (! is_array($row) || trim(AiScalarText::from($row['job_title'] ?? '')) === '') {
                continue;
            }

            $fingerprint = CvExtractionSectionFingerprint::experience($row);
            if ($fingerprint === '' || isset($existingExperiences[$fingerprint])) {
                continue;
            }

            $startDate = $this->resolveRequiredStartDate($row);
            if ($startDate === null) {
                continue;
            }

            Experience::query()->create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'job_title' => AiScalarText::from($row['job_title']),
                'company_name' => AiScalarText::from($row['company_name'] ?? ''),
                'country_id' => $this->countryResolver->resolveId(AiScalarText::from($row['country_name'] ?? '')),
                'city_name' => AiScalarText::from($row['city_name'] ?? ''),
                'start_date' => $startDate,
                'end_date' => $this->parseDateOrNull($row['end_date'] ?? null),
                'is_current' => (bool) ($row['is_current'] ?? false),
                'responsibilities' => AiScalarText::from($row['responsibilities'] ?? ''),
                'achievements' => AiScalarText::from($row['achievements'] ?? ''),
            ]);
            $existingExperiences[$fingerprint] = true;
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyCertificationSections(User $user, UserDocument $document, array $draft): void
    {
        $existingCertifications = $this->existingCertificationFingerprints($user->id);

        foreach (is_array($draft['certifications'] ?? null) ? $draft['certifications'] : [] as $row) {
            if (! is_array($row) || trim((string) ($row['name'] ?? '')) === '') {
                continue;
            }

            $fingerprint = CvExtractionSectionFingerprint::certification($row);
            if ($fingerprint === '' || isset($existingCertifications[$fingerprint])) {
                continue;
            }

            Certification::query()->create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'name' => (string) $row['name'],
                'issuing_organization' => (string) ($row['issuing_organization'] ?? ''),
                'issue_date' => $this->parseDateOrNull($row['issue_date'] ?? null),
                'expiry_date' => $this->parseDateOrNull($row['expiry_date'] ?? null),
                'credential_id' => (string) ($row['credential_id'] ?? ''),
                'credential_url' => (string) ($row['credential_url'] ?? ''),
            ]);
            $existingCertifications[$fingerprint] = true;
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function applyCvSections(User $user, UserDocument $document, array $draft): void
    {
        $this->applyEducationSections($user, $document, $draft);
        $this->applyExperienceSections($user, $document, $draft);
        $this->applyCertificationSections($user, $document, $draft);

        $existingInternships = $this->existingInternshipFingerprints($user->id);
        $existingSkillIds = $this->existingSkillIds($user->id);

        $this->collapseDuplicateUserLanguages($user->id);
        $this->collapseDuplicateEducations($user->id);
        $this->collapseDuplicateExperiences($user->id);

        foreach (is_array($draft['languages'] ?? null) ? $draft['languages'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $languageId = isset($row['resolved_language_id'])
                ? (int) $row['resolved_language_id']
                : $this->languageResolver->resolveId((string) ($row['language_code'] ?? $row['language_name'] ?? ''));
            $levelId = isset($row['resolved_language_level_id'])
                ? (int) $row['resolved_language_level_id']
                : $this->languageResolver->resolveLevelId((string) ($row['proficiency_level'] ?? ''));

            if ($languageId === null || $levelId === null) {
                continue;
            }

            $this->upsertUserLanguage($user->id, $languageId, $levelId);
        }

        foreach (is_array($draft['internships'] ?? null) ? $draft['internships'] : [] as $row) {
            if (! is_array($row) || trim((string) ($row['title'] ?? '')) === '') {
                continue;
            }

            $fingerprint = CvExtractionSectionFingerprint::internship($row);
            if ($fingerprint === '' || isset($existingInternships[$fingerprint])) {
                continue;
            }

            UserInternship::query()->create([
                'user_id' => $user->id,
                'type' => $this->mapInternshipType((string) ($row['type'] ?? 'internship')),
                'title' => (string) $row['title'],
                'organization' => (string) ($row['organization'] ?? ''),
                'location' => (string) ($row['location'] ?? ''),
                'start_date' => $this->parseDateOrNull($row['start_date'] ?? null) ?? now()->toDateString(),
                'end_date' => $this->parseDateOrNull($row['end_date'] ?? null),
                'is_current' => (bool) ($row['is_current'] ?? false),
                'description' => (string) ($row['description'] ?? ''),
                'certificate_document_id' => $document->id,
                'status' => ($row['is_current'] ?? false) ? 'ONGOING' : 'COMPLETED',
            ]);
            $existingInternships[$fingerprint] = true;
        }

        foreach (is_array($draft['skills'] ?? null) ? $draft['skills'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $skillId = isset($row['resolved_skill_id'])
                ? (int) $row['resolved_skill_id']
                : $this->skillResolver->resolveId((string) ($row['name'] ?? ''));

            if ($skillId === null || $skillId <= 0 || isset($existingSkillIds[$skillId])) {
                continue;
            }

            UserSkill::query()->create([
                'user_id' => $user->id,
                'skill_id' => $skillId,
                'level' => 'INTERMEDIATE',
            ]);
            $existingSkillIds[$skillId] = true;
        }

        foreach (is_array($draft['interests'] ?? null) ? $draft['interests'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['name'] ?? $row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $exists = InterestAndHobby::query()
                ->where('user_id', $user->id)
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
                ->exists();

            if ($exists) {
                continue;
            }

            InterestAndHobby::query()->create([
                'user_id' => $user->id,
                'title' => $title,
            ]);
        }
    }

    private function mapInternshipType(string $type): string
    {
        return match (strtolower($type)) {
            'academic', 'academic_project' => 'ACADEMIC',
            'professional', 'employment' => 'PROFESSIONAL',
            default => 'PROFESSIONAL',
        };
    }

    /**
     * @return array<string, true>
     */
    private function existingEducationFingerprints(int $userId): array
    {
        $fingerprints = [];

        foreach (Education::query()->where('user_id', $userId)->get() as $education) {
            $fingerprints[CvExtractionSectionFingerprint::education([
                'degree' => $education->degree,
                'institution_name' => $education->institution_name,
                'start_date' => $education->start_date?->toDateString(),
            ])] = true;
        }

        return $fingerprints;
    }

    /**
     * @return array<string, true>
     */
    private function existingExperienceFingerprints(int $userId): array
    {
        $fingerprints = [];

        foreach (Experience::query()->where('user_id', $userId)->get() as $experience) {
            $fingerprints[CvExtractionSectionFingerprint::experience([
                'job_title' => $experience->job_title,
                'company_name' => $experience->company_name,
                'start_date' => $experience->start_date?->toDateString(),
            ])] = true;
        }

        return $fingerprints;
    }

    /**
     * @return array<string, true>
     */
    private function existingCertificationFingerprints(int $userId): array
    {
        $fingerprints = [];

        foreach (Certification::query()->where('user_id', $userId)->get() as $certification) {
            $fingerprints[CvExtractionSectionFingerprint::certification([
                'name' => $certification->name,
                'issuing_organization' => $certification->issuing_organization,
            ])] = true;
        }

        return $fingerprints;
    }

    /**
     * @return array<string, true>
     */
    private function existingInternshipFingerprints(int $userId): array
    {
        $fingerprints = [];

        foreach (UserInternship::query()->where('user_id', $userId)->get() as $internship) {
            $fingerprints[CvExtractionSectionFingerprint::internship([
                'title' => $internship->title,
                'organization' => $internship->organization,
                'start_date' => $internship->start_date?->toDateString(),
            ])] = true;
        }

        return $fingerprints;
    }

    /**
     * @return array<int, true>
     */
    private function existingSkillIds(int $userId): array
    {
        return UserSkill::query()
            ->where('user_id', $userId)
            ->pluck('skill_id')
            ->mapWithKeys(static fn (int $skillId): array => [$skillId => true])
            ->all();
    }

    private function upsertUserLanguage(int $userId, int $languageId, int $levelId): void
    {
        $levelRanks = LanguageLevel::query()->pluck('sort_order', 'id');
        $newRank = (int) ($levelRanks[$levelId] ?? 0);

        $existing = UserLanguage::query()
            ->where('user_id', $userId)
            ->where('language_id', $languageId)
            ->orderByDesc('id')
            ->first();

        if ($existing === null) {
            UserLanguage::query()->create([
                'user_id' => $userId,
                'language_id' => $languageId,
                'language_level_id' => $levelId,
                'is_approved' => false,
            ]);

            return;
        }

        $currentRank = (int) ($levelRanks[$existing->language_level_id] ?? 0);
        if ($newRank >= $currentRank) {
            $existing->update(['language_level_id' => $levelId]);
        }
    }

    private function collapseDuplicateUserLanguages(int $userId): void
    {
        $levelRanks = LanguageLevel::query()->pluck('sort_order', 'id');
        $grouped = UserLanguage::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->groupBy('language_id');

        foreach ($grouped as $entries) {
            if ($entries->count() <= 1) {
                continue;
            }

            $best = $entries->sortByDesc(
                static fn (UserLanguage $language): int => (int) ($levelRanks[$language->language_level_id] ?? 0),
            )->first();

            foreach ($entries as $entry) {
                if ($best !== null && $entry->id !== $best->id) {
                    $entry->delete();
                }
            }
        }
    }

    private function collapseDuplicateEducations(int $userId): void
    {
        $this->collapseDuplicatesByFingerprint(
            Education::query()->where('user_id', $userId)->get(),
            static fn (Education $education): string => CvExtractionSectionFingerprint::education([
                'degree' => $education->degree,
                'institution_name' => $education->institution_name,
                'start_date' => $education->start_date?->toDateString(),
            ]),
        );
    }

    private function collapseDuplicateExperiences(int $userId): void
    {
        $this->collapseDuplicatesByFingerprint(
            Experience::query()->where('user_id', $userId)->get(),
            static fn (Experience $experience): string => CvExtractionSectionFingerprint::experience([
                'job_title' => $experience->job_title,
                'company_name' => $experience->company_name,
                'start_date' => $experience->start_date?->toDateString(),
            ]),
        );
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Support\Collection<int, T>  $records
     * @param  callable(T): string  $fingerprint
     */
    private function collapseDuplicatesByFingerprint($records, callable $fingerprint): void
    {
        $grouped = [];

        foreach ($records as $record) {
            $key = $fingerprint($record);
            if ($key === '') {
                continue;
            }

            $grouped[$key][] = $record;
        }

        foreach ($grouped as $entries) {
            if (count($entries) <= 1) {
                continue;
            }

            usort($entries, static fn ($a, $b): int => (int) $b->id <=> (int) $a->id);
            array_shift($entries);

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveRequiredStartDate(array $row): ?string
    {
        return $this->parseDateOrNull($row['start_date'] ?? null)
            ?? $this->parseDateOrNull($row['end_date'] ?? null);
    }

    private function parseDateOrNull(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDateOrString(string $value): string
    {
        return $this->parseDateOrNull($value) ?? $value;
    }
}
