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
use App\Core\Domain\Shared\Ai\Support\AiScalarText;
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
            in_array($code, ['DIPLOMA', 'TRANSCRIPT', 'SUCCESS_CERTIFICATE'], true) => $this->enrichDiplomaDocumentDraft($draft),
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
        $draft = $this->coerceCvListScalars($draft);
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
     * Les LLM renvoient souvent responsibilities/achievements/description en listes.
     *
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function coerceCvListScalars(array $draft): array
    {
        $experienceKeys = [
            'job_title', 'company_name', 'city_name', 'country_name',
            'responsibilities', 'achievements', 'description', 'experience_type',
        ];
        $educationKeys = [
            'degree', 'institution_name', 'field_of_study', 'city_name', 'country_name', 'grade',
        ];
        $internshipKeys = [
            'title', 'job_title', 'organization', 'company_name', 'location', 'description', 'type',
        ];
        $certKeys = ['name', 'issuing_organization', 'credential_id', 'credential_url'];
        $formationKeys = ['name', 'title', 'organization', 'provider', 'description'];

        foreach (is_array($draft['experiences'] ?? null) ? $draft['experiences'] : [] as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($experienceKeys as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = AiScalarText::from($row[$key], $key === 'responsibilities' || $key === 'achievements' ? "\n" : ' ');
                }
            }
            $draft['experiences'][$i] = $row;
        }

        foreach (is_array($draft['educations'] ?? null) ? $draft['educations'] : [] as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($educationKeys as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = AiScalarText::from($row[$key]);
                }
            }
            $draft['educations'][$i] = $row;
        }

        foreach (is_array($draft['internships'] ?? null) ? $draft['internships'] : [] as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($internshipKeys as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = AiScalarText::from($row[$key], $key === 'description' ? "\n" : ' ');
                }
            }
            $draft['internships'][$i] = $row;
        }

        foreach (is_array($draft['certifications'] ?? null) ? $draft['certifications'] : [] as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($certKeys as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = AiScalarText::from($row[$key]);
                }
            }
            $draft['certifications'][$i] = $row;
        }

        foreach (is_array($draft['formations'] ?? null) ? $draft['formations'] : [] as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($formationKeys as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = AiScalarText::from($row[$key]);
                }
            }
            $draft['formations'][$i] = $row;
        }

        $profile = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];
        foreach ([
            'first_name', 'last_name', 'full_name', 'bio', 'address', 'place_of_birth',
            'gender', 'marital_status', 'nationality_country_name', 'residence_country_name',
            'residence_city_name', 'phone_number2', 'phone_number3', 'email_institutional',
        ] as $key) {
            if (array_key_exists($key, $profile)) {
                $profile[$key] = AiScalarText::from($profile[$key], $key === 'bio' ? ' ' : ' ');
            }
        }
        $draft['user_profile'] = $profile;

        if (array_key_exists('notes', $draft)) {
            $draft['notes'] = AiScalarText::from($draft['notes']);
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
        $jobTitle = trim(AiScalarText::from($work['job_title'] ?? ''));
        $companyName = trim(AiScalarText::from($work['company_name'] ?? ''));

        if ($work !== [] && $jobTitle !== '') {
            if ($this->looksLikeAcademicCredential($jobTitle, $companyName)) {
                $education = $this->organizationDisambiguator->disambiguateEducation([
                    'degree' => $jobTitle,
                    'institution_name' => $companyName,
                    'field_of_study' => AiScalarText::from($work['responsibilities'] ?? ''),
                    'city_name' => AiScalarText::from($work['city_name'] ?? ''),
                    'country_name' => AiScalarText::from($work['country_name'] ?? ''),
                    'start_date' => $work['start_date'] ?? null,
                    'end_date' => $work['end_date'] ?? null,
                ]);
                $draft['educations'] = [$education];
                $draft['experiences'] = [];
                $draft['notes'] = trim(implode("\n", array_filter([
                    AiScalarText::from($draft['notes'] ?? ''),
                    'Document académique détecté (diplôme / université) alors que le type déposé est un certificat de travail. '
                    .'Les données ont été reclassées en formation. Re-déposez plutôt comme DIPLOMA si besoin.',
                ])));
            } else {
                $experience = $this->organizationDisambiguator->disambiguateExperience([
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'city_name' => AiScalarText::from($work['city_name'] ?? ''),
                    'country_name' => AiScalarText::from($work['country_name'] ?? ''),
                    'start_date' => $work['start_date'] ?? null,
                    'end_date' => $work['end_date'] ?? null,
                    'is_current' => (bool) ($work['is_current'] ?? false),
                    'responsibilities' => AiScalarText::from($work['responsibilities'] ?? ''),
                    'experience_type' => 'employment',
                    'is_professional' => true,
                ]);
                $draft['experiences'] = [$experience];
            }
        }

        unset($draft['work_certificate']);

        return $draft;
    }

    private function looksLikeAcademicCredential(string $jobTitle, string $companyName): bool
    {
        $haystack = mb_strtolower(trim($jobTitle.' '.$companyName));
        if ($haystack === '') {
            return false;
        }

        $patterns = [
            'master',
            'licence',
            'bachelor',
            'doctorat',
            'phd',
            'diplôme',
            'diplome',
            'baccalauréat',
            'baccalaureat',
            'attestation de réussite',
            'attestation de reussite',
            'relevé de notes',
            'releve de notes',
            'université',
            'universite',
            'university',
            'faculté',
            'faculte',
            'école ',
            'ecole ',
            'college',
            'collège',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($haystack, $pattern)) {
                return true;
            }
        }

        return false;
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
        $name = trim(AiScalarText::from($cert['name'] ?? ''));
        $issuer = trim(AiScalarText::from($cert['issuing_organization'] ?? ''));

        if ($cert !== [] && $name !== '') {
            if ($this->looksLikeAcademicCredential($name, $issuer)) {
                $draft['educations'] = [
                    $this->organizationDisambiguator->disambiguateEducation([
                        'degree' => $name,
                        'institution_name' => $issuer,
                        'field_of_study' => AiScalarText::from($cert['field_of_study'] ?? ''),
                        'start_date' => $cert['issue_date'] ?? null,
                        'end_date' => $cert['issue_date'] ?? null,
                        'grade' => AiScalarText::from($cert['credential_id'] ?? ''),
                    ]),
                ];
                $draft['certifications'] = [];
                $draft['notes'] = trim(implode("\n", array_filter([
                    AiScalarText::from($draft['notes'] ?? ''),
                    'Document académique détecté alors que le type déposé est une certification. '
                    .'Les données ont été reclassées en formation. Re-déposez plutôt comme DIPLOMA si besoin.',
                ])));
            } else {
                $draft['certifications'] = [[
                    'name' => $name,
                    'issuing_organization' => $issuer,
                    'issue_date' => $cert['issue_date'] ?? null,
                    'expiry_date' => $cert['expiry_date'] ?? null,
                    'credential_id' => AiScalarText::from($cert['credential_id'] ?? ''),
                    'credential_url' => AiScalarText::from($cert['credential_url'] ?? ''),
                ]];
            }
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
        if ($education !== [] && trim(AiScalarText::from($education['degree'] ?? '')) !== '') {
            $normalized = $education;
            foreach (['degree', 'institution_name', 'field_of_study', 'city_name', 'country_name', 'grade'] as $key) {
                if (array_key_exists($key, $normalized)) {
                    $normalized[$key] = AiScalarText::from($normalized[$key]);
                }
            }
            $draft['educations'] = [
                $this->organizationDisambiguator->disambiguateEducation($normalized),
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
            isset($profile['first_name']) ? AiScalarText::nullable($profile['first_name']) : null,
            isset($profile['last_name']) ? AiScalarText::nullable($profile['last_name']) : null,
            isset($profile['full_name'])
                ? AiScalarText::nullable($profile['full_name'])
                : (isset($draft['full_name']) ? AiScalarText::nullable($draft['full_name']) : null),
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
            $countryHint = AiScalarText::from($profile['nationality_country_name'] ?? $profile['residence_country_name'] ?? '');
            foreach (['phone_number2', 'phone_number3'] as $phoneField) {
                if (! empty($profile[$phoneField])) {
                    $profile[$phoneField] = $this->phoneNormalizer->normalize(AiScalarText::from($profile[$phoneField]), $countryHint) ?? $profile[$phoneField];
                }
            }

            if (! empty($profile['nationality_country_name'])) {
                $profile['resolved_nationality_country_id'] = $this->countryResolver->resolveId(AiScalarText::from($profile['nationality_country_name']));
            }

            if (! empty($profile['marital_status'])) {
                $normalizedMaritalStatus = $this->maritalStatusNormalizer->normalize(AiScalarText::from($profile['marital_status']));
                if ($normalizedMaritalStatus !== null) {
                    $profile['marital_status'] = $normalizedMaritalStatus;
                }
            }
        }

        if (! empty($profile['gender'])) {
            $normalizedGender = $this->genderNormalizer->normalize(AiScalarText::from($profile['gender']));
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
            $document['resolved_issuing_country_id'] = $this->countryResolver->resolveId(AiScalarText::from($document['issuing_country_name']));
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
            isset($profile['first_name']) ? AiScalarText::nullable($profile['first_name']) : null,
            isset($profile['last_name']) ? AiScalarText::nullable($profile['last_name']) : null,
            isset($profile['full_name'])
                ? AiScalarText::nullable($profile['full_name'])
                : (isset($draft['full_name']) ? AiScalarText::nullable($draft['full_name']) : null),
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
            $profile['full_name'] = trim(AiScalarText::from($profile['last_name'] ?? '').' '.AiScalarText::from($profile['first_name'] ?? ''));
        }

        $countryHint = AiScalarText::from($profile['nationality_country_name'] ?? $profile['residence_country_name'] ?? '');
        if ($countryHint === '' && ! empty($profile['residence_city_name'])) {
            $inferred = $this->countryResolver->resolveNameFromCity(AiScalarText::from($profile['residence_city_name']));
            if ($inferred !== null) {
                $profile['residence_country_name'] = $inferred;
                $countryHint = $inferred;
            }
        }

        foreach (['phone_number2', 'phone_number3'] as $phoneField) {
            if (! empty($profile[$phoneField])) {
                $profile[$phoneField] = $this->phoneNormalizer->normalize(AiScalarText::from($profile[$phoneField]), $countryHint) ?? $profile[$phoneField];
            }
        }

        if (! empty($profile['nationality_country_name'])) {
            $profile['resolved_nationality_country_id'] = $this->countryResolver->resolveId(AiScalarText::from($profile['nationality_country_name']));
        }

        $bio = $this->resolveBio($profile, $draft);
        if ($bio !== '') {
            $profile['bio'] = $bio;
        }

        if (! empty($profile['marital_status'])) {
            $normalizedMaritalStatus = $this->maritalStatusNormalizer->normalize(AiScalarText::from($profile['marital_status']));
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
        $bio = AiScalarText::from($value, ' ');
        if ($bio === '') {
            return '';
        }

        $bio = trim(preg_replace('/\s+/u', ' ', $bio) ?? $bio);
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

            $jobTitle = AiScalarText::from($row['job_title'] ?? '');
            $companyName = AiScalarText::from($row['company_name'] ?? '');

            if ($this->looksLikeAcademicCredential($jobTitle, $companyName)
                || strtolower(AiScalarText::from($row['experience_type'] ?? '')) === 'academic_project'
            ) {
                $educations = is_array($draft['educations'] ?? null) ? $draft['educations'] : [];
                $educations[] = $this->organizationDisambiguator->disambiguateEducation([
                    'degree' => $jobTitle,
                    'institution_name' => $companyName,
                    'field_of_study' => AiScalarText::from($row['responsibilities'] ?? $row['achievements'] ?? ''),
                    'city_name' => AiScalarText::from($row['city_name'] ?? ''),
                    'country_name' => AiScalarText::from($row['country_name'] ?? ''),
                    'start_date' => $row['start_date'] ?? null,
                    'end_date' => $row['end_date'] ?? null,
                ]);
                $draft['educations'] = $educations;

                continue;
            }

            $type = strtolower(AiScalarText::from($row['experience_type'] ?? 'employment'));
            $isProfessional = array_key_exists('is_professional', $row)
                ? (bool) $row['is_professional']
                : ! in_array($type, ['internship', 'volunteer', 'academic_project', 'training', 'other'], true);

            if ($this->isInternshipRow($row) || ! $isProfessional || $type === 'internship') {
                $internRow = [
                    'title' => $jobTitle,
                    'organization' => $companyName,
                    'location' => trim(AiScalarText::from($row['city_name'] ?? '').' '.AiScalarText::from($row['country_name'] ?? '')),
                    'start_date' => $row['start_date'] ?? null,
                    'end_date' => $row['end_date'] ?? null,
                    'is_current' => (bool) ($row['is_current'] ?? false),
                    'description' => trim(AiScalarText::from($row['responsibilities'] ?? '').' '.AiScalarText::from($row['achievements'] ?? '')),
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
        $type = strtolower(AiScalarText::from($row['experience_type'] ?? ''));
        if (in_array($type, ['internship', 'stage', 'stagiaire', 'academic_project', 'volunteer', 'training'], true)) {
            return true;
        }

        $blob = mb_strtolower(implode(' ', array_filter([
            AiScalarText::from($row['job_title'] ?? ''),
            AiScalarText::from($row['company_name'] ?? ''),
            AiScalarText::from($row['responsibilities'] ?? ''),
            AiScalarText::from($row['achievements'] ?? ''),
            AiScalarText::from($row['description'] ?? ''),
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

            $title = trim(AiScalarText::from($row['title'] ?? $row['job_title'] ?? ''));
            $organization = trim(AiScalarText::from($row['organization'] ?? $row['company_name'] ?? ''));
            if ($title === '' && $organization === '') {
                continue;
            }

            $internships[] = [
                'title' => $title,
                'organization' => $organization,
                'location' => AiScalarText::from($row['location'] ?? ''),
                'start_date' => $row['start_date'] ?? null,
                'end_date' => $row['end_date'] ?? null,
                'is_current' => (bool) ($row['is_current'] ?? false),
                'description' => AiScalarText::from($row['description'] ?? ''),
                'type' => AiScalarText::from($row['type'] ?? 'internship') ?: 'internship',
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
                    $inferred = $this->countryResolver->resolveNameFromCity(AiScalarText::from($row['city_name']));
                    if ($inferred !== null) {
                        $row['country_name'] = $inferred;
                    }
                }
                if (! empty($row['country_name'])) {
                    $row['resolved_country_id'] = $this->countryResolver->resolveId(AiScalarText::from($row['country_name']));
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

            $name = AiScalarText::from($row['language_name'] ?? $row['language'] ?? $row['name'] ?? '');
            $code = AiScalarText::from($row['language_code'] ?? '');
            $proficiency = $this->languageProficiencyNormalizer->normalize(
                AiScalarText::from($row['proficiency_level'] ?? $row['level'] ?? ''),
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
                $name = trim(AiScalarText::from($item['name']));
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
