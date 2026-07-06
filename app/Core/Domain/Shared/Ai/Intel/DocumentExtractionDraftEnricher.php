<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Support\CountryNameResolver;
use App\Core\Domain\Identity\Support\Document\CvDraftSectionDeduplicator;
use App\Core\Domain\Identity\Support\GenderNormalizer;
use App\Core\Domain\Identity\Support\LanguageCatalogResolver;
use App\Core\Domain\Identity\Support\LanguageProficiencyNormalizer;
use App\Core\Domain\Identity\Support\MaritalStatusNormalizer;
use App\Core\Domain\Identity\Support\OrganizationNameDisambiguator;
use App\Core\Domain\Identity\Support\PersonNameParser;
use App\Core\Domain\Identity\Support\PhoneNumberNormalizer;
use App\Core\Domain\Identity\Support\SkillCatalogResolver;
use App\Core\Domain\Shared\Ai\Support\InterestsDraftCollector;
use App\Core\Domain\Shared\Ai\Support\ProfileBundleDraftNormalizer;

/**
 * Post-traitement intelligent du brouillon IA : normalisation, résolution catalogue, reclassement sections.
 */
final class DocumentExtractionDraftEnricher
{
    public function __construct(
        private readonly PersonNameParser $nameParser,
        private readonly PhoneNumberNormalizer $phoneNormalizer,
        private readonly CountryNameResolver $countryResolver,
        private readonly LanguageCatalogResolver $languageResolver,
        private readonly LanguageProficiencyNormalizer $languageProficiencyNormalizer,
        private readonly MaritalStatusNormalizer $maritalStatusNormalizer,
        private readonly GenderNormalizer $genderNormalizer,
        private readonly SkillCatalogResolver $skillResolver,
        private readonly OrganizationNameDisambiguator $organizationDisambiguator,
        private readonly CvDraftSectionDeduplicator $draftDeduplicator,
    ) {}

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    public function enrich(array $draft, string $documentTypeCode = 'CV'): array
    {
        $code = strtoupper($documentTypeCode);

        return match (true) {
            $code === 'CV' => $this->enrichCvDraft($draft),
            DocumentExtractionProfileRegistry::isIdentityDocument($code) => $this->enrichIdentityDocumentDraft($draft),
            $code === 'BIRTH_CERTIFICATE' => $this->enrichBirthCertificateDraft($draft),
            $code === 'WORK_CERTIFICATE' => $this->enrichWorkCertificateDraft($draft),
            in_array($code, ['PROFESSIONAL_CERTIFICATION', 'TRAINING_CERTIFICATE'], true) => $this->enrichCertificationDocumentDraft($draft),
            in_array($code, ['DIPLOMA', 'TRANSCRIPT'], true) => $this->enrichDiplomaDocumentDraft($draft),
            default => ProfileBundleDraftNormalizer::normalize($draft),
        };
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichCvDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $draft = $this->enrichProfile($draft);
        $draft = $this->reclassifyExperiences($draft);
        $draft = $this->recoverInternships($draft);
        $draft = $this->enrichOrganizationFields($draft);
        $draft = $this->enrichLocationFields($draft);
        $draft = $this->enrichLanguages($draft);
        $draft = $this->normalizeSkills($draft);
        $draft = $this->normalizeInterests($draft);
        $draft = $this->draftDeduplicator->deduplicate($draft);

        foreach (['educations', 'experiences', 'internships', 'certifications', 'languages', 'skills', 'formations', 'interests'] as $key) {
            if (! isset($draft[$key]) || ! is_array($draft[$key])) {
                $draft[$key] = [];
            }
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichIdentityDocumentDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $draft = $this->enrichIdentityProfile($draft);
        $draft = $this->enrichUserDocumentMetadata($draft);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichBirthCertificateDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $birthRecord = is_array($draft['birth_record'] ?? null) ? $draft['birth_record'] : [];
        $profile = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];

        foreach ([
            'date_of_birth' => 'date_of_birth',
            'place_of_birth' => 'place_of_birth',
            'gender' => 'gender',
        ] as $from => $to) {
            if (empty($profile[$to]) && ! empty($birthRecord[$from])) {
                $profile[$to] = $birthRecord[$from];
            }
        }

        $draft['user_profile'] = $profile;
        $draft = $this->enrichIdentityProfile($draft);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichWorkCertificateDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $draft = $this->enrichIdentityProfile($draft, namesOnly: true);

        $work = is_array($draft['work_certificate'] ?? null) ? $draft['work_certificate'] : [];
        if ($work !== [] && trim((string) ($work['job_title'] ?? '')) !== '') {
            $experience = $this->organizationDisambiguator->disambiguateExperience([
                'job_title' => (string) ($work['job_title'] ?? ''),
                'company_name' => (string) ($work['company_name'] ?? ''),
                'city_name' => (string) ($work['city_name'] ?? ''),
                'country_name' => (string) ($work['country_name'] ?? ''),
                'start_date' => $work['start_date'] ?? null,
                'end_date' => $work['end_date'] ?? null,
                'is_current' => (bool) ($work['is_current'] ?? false),
                'responsibilities' => (string) ($work['responsibilities'] ?? ''),
                'experience_type' => 'employment',
                'is_professional' => true,
            ]);
            $draft['experiences'] = [$experience];
        }

        unset($draft['work_certificate']);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichCertificationDocumentDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $draft = $this->enrichIdentityProfile($draft, namesOnly: true);

        $cert = is_array($draft['certification'] ?? null) ? $draft['certification'] : [];
        if ($cert !== [] && trim((string) ($cert['name'] ?? '')) !== '') {
            $name = trim((string) $cert['name']);
            $draft['certifications'] = [[
                'name' => $name,
                'issuing_organization' => (string) ($cert['issuing_organization'] ?? ''),
                'issue_date' => $cert['issue_date'] ?? null,
                'expiry_date' => $cert['expiry_date'] ?? null,
                'credential_id' => (string) ($cert['credential_id'] ?? ''),
                'credential_url' => (string) ($cert['credential_url'] ?? ''),
            ]];
        }

        unset($draft['certification']);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichDiplomaDocumentDraft(array $draft): array
    {
        $draft = ProfileBundleDraftNormalizer::normalize($draft);
        $draft = $this->enrichIdentityProfile($draft, namesOnly: true);

        $education = is_array($draft['education'] ?? null) ? $draft['education'] : [];
        if ($education !== [] && trim((string) ($education['degree'] ?? '')) !== '') {
            $draft['educations'] = [
                $this->organizationDisambiguator->disambiguateEducation($education),
            ];
        }

        unset($draft['education']);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichIdentityProfile(array $draft, bool $namesOnly = false): array
    {
        $profile = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];
        $parsed = $this->nameParser->parse(
            isset($profile['first_name']) ? (string) $profile['first_name'] : null,
            isset($profile['last_name']) ? (string) $profile['last_name'] : null,
            isset($profile['full_name']) ? (string) $profile['full_name'] : (isset($draft['full_name']) ? (string) $draft['full_name'] : null),
        );
        if ($parsed['first_name'] !== '') {
            $profile['first_name'] = $parsed['first_name'];
        }
        if ($parsed['last_name'] !== '') {
            $profile['last_name'] = $parsed['last_name'];
        }
        if ($parsed['full_name'] !== '') {
            $profile['full_name'] = $parsed['full_name'];
        }

        if (! $namesOnly) {
            $countryHint = (string) ($profile['nationality_country_name'] ?? $profile['residence_country_name'] ?? '');
            foreach (['phone_number2', 'phone_number3'] as $phoneField) {
                if (! empty($profile[$phoneField])) {
                    $profile[$phoneField] = $this->phoneNormalizer->normalize((string) $profile[$phoneField], $countryHint) ?? $profile[$phoneField];
                }
            }

            if (! empty($profile['nationality_country_name'])) {
                $profile['resolved_nationality_country_id'] = $this->countryResolver->resolveId((string) $profile['nationality_country_name']);
            }

            if (! empty($profile['marital_status'])) {
                $normalizedMaritalStatus = $this->maritalStatusNormalizer->normalize((string) $profile['marital_status']);
                if ($normalizedMaritalStatus !== null) {
                    $profile['marital_status'] = $normalizedMaritalStatus;
                }
            }
        }

        if (! empty($profile['gender'])) {
            $normalizedGender = $this->genderNormalizer->normalize((string) $profile['gender']);
            if ($normalizedGender !== null) {
                $profile['gender'] = $normalizedGender;
            }
        }

        $draft['user_profile'] = $profile;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichUserDocumentMetadata(array $draft): array
    {
        $document = is_array($draft['user_document'] ?? null) ? $draft['user_document'] : [];
        if (! empty($document['issuing_country_name'])) {
            $document['resolved_issuing_country_id'] = $this->countryResolver->resolveId((string) $document['issuing_country_name']);
        }
        $draft['user_document'] = $document;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichProfile(array $draft): array
    {
        $profile = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];
        $parsed = $this->nameParser->parse(
            isset($profile['first_name']) ? (string) $profile['first_name'] : null,
            isset($profile['last_name']) ? (string) $profile['last_name'] : null,
            isset($profile['full_name']) ? (string) $profile['full_name'] : (isset($draft['full_name']) ? (string) $draft['full_name'] : null),
        );
        if ($parsed['first_name'] !== '') {
            $profile['first_name'] = $parsed['first_name'];
        }
        if ($parsed['last_name'] !== '') {
            $profile['last_name'] = $parsed['last_name'];
        }
        if ($parsed['full_name'] !== '') {
            $profile['full_name'] = $parsed['full_name'];
        } elseif (($profile['first_name'] ?? '') !== '' || ($profile['last_name'] ?? '') !== '') {
            $profile['full_name'] = trim(((string) ($profile['last_name'] ?? '')).' '.((string) ($profile['first_name'] ?? '')));
        }

        $countryHint = (string) ($profile['nationality_country_name'] ?? $profile['residence_country_name'] ?? '');
        if ($countryHint === '' && ! empty($profile['residence_city_name'])) {
            $inferred = $this->countryResolver->resolveNameFromCity((string) $profile['residence_city_name']);
            if ($inferred !== null) {
                $profile['residence_country_name'] = $inferred;
                $countryHint = $inferred;
            }
        }

        foreach (['phone_number2', 'phone_number3'] as $phoneField) {
            if (! empty($profile[$phoneField])) {
                $profile[$phoneField] = $this->phoneNormalizer->normalize((string) $profile[$phoneField], $countryHint) ?? $profile[$phoneField];
            }
        }

        if (! empty($profile['nationality_country_name'])) {
            $profile['resolved_nationality_country_id'] = $this->countryResolver->resolveId((string) $profile['nationality_country_name']);
        }

        $bio = $this->resolveBio($profile, $draft);
        if ($bio !== '') {
            $profile['bio'] = $bio;
        }

        if (! empty($profile['marital_status'])) {
            $normalizedMaritalStatus = $this->maritalStatusNormalizer->normalize((string) $profile['marital_status']);
            if ($normalizedMaritalStatus !== null) {
                $profile['marital_status'] = $normalizedMaritalStatus;
            }
        }

        $draft['user_profile'] = $profile;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $draft
     */
    private function resolveBio(array $profile, array $draft): string
    {
        $candidates = [
            $profile['bio'] ?? null,
            $draft['bio'] ?? null,
            $draft['summary'] ?? null,
            $draft['profile_summary'] ?? null,
            $draft['professional_summary'] ?? null,
            $draft['about'] ?? null,
            $draft['presentation'] ?? null,
            $draft['objectif_professionnel'] ?? null,
            $draft['objectif'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $bio = $this->normalizeBioText($candidate);
            if ($bio !== '') {
                return $bio;
            }
        }

        return '';
    }

    private function normalizeBioText(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $bio = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($bio === '') {
            return '';
        }

        // Ignore les fragments trop courts pour être une vraie bio (titre de poste, mot-clé isolé).
        if (mb_strlen($bio) < 40 && ! str_contains($bio, '.')) {
            return '';
        }

        return $bio;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function reclassifyExperiences(array $draft): array
    {
        $experiences = [];
        $internships = is_array($draft['internships'] ?? null) ? $draft['internships'] : [];

        foreach (is_array($draft['experiences'] ?? null) ? $draft['experiences'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $type = strtolower((string) ($row['experience_type'] ?? 'employment'));
            $isProfessional = array_key_exists('is_professional', $row)
                ? (bool) $row['is_professional']
                : ! in_array($type, ['internship', 'volunteer', 'academic_project', 'training', 'other'], true);

            if ($this->isInternshipRow($row) || ! $isProfessional || $type === 'internship') {
                $internRow = [
                    'title' => (string) ($row['job_title'] ?? ''),
                    'organization' => (string) ($row['company_name'] ?? ''),
                    'location' => trim(((string) ($row['city_name'] ?? '')).' '.((string) ($row['country_name'] ?? ''))),
                    'start_date' => $row['start_date'] ?? null,
                    'end_date' => $row['end_date'] ?? null,
                    'is_current' => (bool) ($row['is_current'] ?? false),
                    'description' => trim(((string) ($row['responsibilities'] ?? '')).' '.((string) ($row['achievements'] ?? ''))),
                    'type' => $type === 'internship' ? 'internship' : $type,
                ];
                $internships[] = $this->organizationDisambiguator->disambiguateInternship($internRow);

                continue;
            }

            $row['is_professional'] = true;
            $experiences[] = $row;
        }

        $draft['experiences'] = $experiences;
        $draft['internships'] = $internships;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isInternshipRow(array $row): bool
    {
        $type = strtolower((string) ($row['experience_type'] ?? ''));
        if (in_array($type, ['internship', 'stage', 'stagiaire', 'academic_project', 'volunteer', 'training'], true)) {
            return true;
        }

        $blob = mb_strtolower(implode(' ', array_filter([
            (string) ($row['job_title'] ?? ''),
            (string) ($row['company_name'] ?? ''),
            (string) ($row['responsibilities'] ?? ''),
            (string) ($row['achievements'] ?? ''),
            (string) ($row['description'] ?? ''),
        ])));

        if ($blob === '') {
            return false;
        }

        return preg_match(
            '/\b(stage|stagiaire|stages|internship|alternance|brand ambassador|stage au|en stage)\b/u',
            $blob,
        ) === 1;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function recoverInternships(array $draft): array
    {
        $internships = [];

        foreach (is_array($draft['internships'] ?? null) ? $draft['internships'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? $row['job_title'] ?? ''));
            $organization = trim((string) ($row['organization'] ?? $row['company_name'] ?? ''));
            if ($title === '' && $organization === '') {
                continue;
            }

            $internships[] = [
                'title' => $title,
                'organization' => $organization,
                'location' => (string) ($row['location'] ?? ''),
                'start_date' => $row['start_date'] ?? null,
                'end_date' => $row['end_date'] ?? null,
                'is_current' => (bool) ($row['is_current'] ?? false),
                'description' => (string) ($row['description'] ?? ''),
                'type' => (string) ($row['type'] ?? 'internship'),
            ];
        }

        $draft['internships'] = $internships;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichLocationFields(array $draft): array
    {
        foreach (['educations', 'experiences'] as $section) {
            foreach (is_array($draft[$section] ?? null) ? $draft[$section] : [] as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (empty($row['country_name']) && ! empty($row['city_name'])) {
                    $inferred = $this->countryResolver->resolveNameFromCity((string) $row['city_name']);
                    if ($inferred !== null) {
                        $row['country_name'] = $inferred;
                    }
                }
                if (! empty($row['country_name'])) {
                    $row['resolved_country_id'] = $this->countryResolver->resolveId((string) $row['country_name']);
                }
                $draft[$section][$index] = $row;
            }
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichOrganizationFields(array $draft): array
    {
        foreach (is_array($draft['educations'] ?? null) ? $draft['educations'] : [] as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $draft['educations'][$index] = $this->organizationDisambiguator->disambiguateEducation($row);
        }

        foreach (is_array($draft['experiences'] ?? null) ? $draft['experiences'] : [] as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $draft['experiences'][$index] = $this->organizationDisambiguator->disambiguateExperience($row);
        }

        foreach (is_array($draft['internships'] ?? null) ? $draft['internships'] : [] as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $draft['internships'][$index] = $this->organizationDisambiguator->disambiguateInternship($row);
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function normalizeInterests(array $draft): array
    {
        $draft['interests'] = InterestsDraftCollector::collect($draft);

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function enrichLanguages(array $draft): array
    {
        $languages = [];
        foreach (is_array($draft['languages'] ?? null) ? $draft['languages'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = (string) ($row['language_name'] ?? $row['language'] ?? $row['name'] ?? '');
            $code = (string) ($row['language_code'] ?? '');
            $proficiency = $this->languageProficiencyNormalizer->normalize(
                (string) ($row['proficiency_level'] ?? $row['level'] ?? ''),
            );

            if ($name === '' && $code === '') {
                continue;
            }

            $languageId = $this->languageResolver->resolveId($code !== '' ? $code : $name);
            $levelId = $this->languageResolver->resolveLevelId($proficiency);

            $languages[] = [
                'language_name' => $name,
                'language_code' => $code,
                'proficiency_level' => $proficiency,
                'resolved_language_id' => $languageId,
                'resolved_language_level_id' => $levelId,
            ];
        }

        $draft['languages'] = $languages;

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function normalizeSkills(array $draft): array
    {
        $skills = [];
        $raw = $draft['skills'] ?? [];

        if (is_string($raw)) {
            $raw = preg_split('/[,;|•\n]+/', $raw) ?: [];
        }

        if (! is_array($raw)) {
            $raw = [];
        }

        foreach ($raw as $item) {
            if (is_string($item)) {
                $name = trim($item);
                if ($name !== '') {
                    $skills[] = [
                        'name' => $name,
                        'resolved_skill_id' => $this->skillResolver->resolveId($name),
                    ];
                }

                continue;
            }
            if (is_array($item) && ! empty($item['name'])) {
                $name = trim((string) $item['name']);
                $skills[] = [
                    'name' => $name,
                    'resolved_skill_id' => $this->skillResolver->resolveId($name),
                ];
            }
        }

        $draft['skills'] = $skills;

        return $draft;
    }
}
