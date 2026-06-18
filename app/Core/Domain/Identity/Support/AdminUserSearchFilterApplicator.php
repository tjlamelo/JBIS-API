<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtres partagés entre la recherche admin (liste) et l'export utilisateurs.
 */
final class AdminUserSearchFilterApplicator
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function apply(Builder $query, array $params): Builder
    {
        $this->applyGlobalSearch($query, $params);
        $this->applyAccountFilters($query, $params);
        $this->applyIdentityFilters($query, $params);
        $this->applyDomainFilters($query, $params);
        $this->applyCareerFilters($query, $params);
        $this->applyEducationFilters($query, $params);
        $this->applySkillFilters($query, $params);
        $this->applyMobilityFilters($query, $params);
        $this->applyDocumentFilters($query, $params);
        $this->applyApplicationFilters($query, $params);
        $this->applyInterviewFilters($query, $params);
        $this->applyTrainingFilters($query, $params);
        $this->applyDateFilters($query, $params);

        if (! empty($params['ids']) && is_array($params['ids'])) {
            $query->whereIn('id', array_values(array_map('intval', $params['ids'])));
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyGlobalSearch(Builder $query, array $params): void
    {
        $search = trim((string) ($params['search'] ?? ''));
        if ($search === '') {
            return;
        }

        $like = '%'.$search.'%';
        $query->where(function (Builder $inner) use ($like): void {
            $inner->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone_number1', 'like', $like)
                ->orWhereHas('profile', function (Builder $profile) use ($like): void {
                    $profile->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('matricule', 'like', $like)
                        ->orWhere('bio', 'like', $like);
                });
        });
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyAccountFilters(Builder $query, array $params): void
    {
        $this->applyTristate($query, $params['active'] ?? null, fn (Builder $q) => $q->where('active', true), fn (Builder $q) => $q->where('active', false));

        $verified = $params['verified'] ?? $params['email_verified'] ?? null;
        $this->applyTristate($query, $verified, fn (Builder $q) => $q->whereNotNull('email_verified_at'), fn (Builder $q) => $q->whereNull('email_verified_at'));

        $role = trim((string) ($params['role'] ?? ''));
        if ($role !== '') {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $role));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyIdentityFilters(Builder $query, array $params): void
    {
        $this->applyTristate(
            $query,
            $params['profile_exists'] ?? null,
            fn (Builder $q) => $q->whereHas('profile'),
            fn (Builder $q) => $q->whereDoesntHave('profile'),
        );

        $profileApproved = $params['profile_approved'] ?? null;
        if ($profileApproved !== null && $profileApproved !== '') {
            $approved = filter_var($profileApproved, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($approved !== null) {
                $query->whereHas('profile', fn (Builder $q) => $q->where('is_approved', $approved));
            }
        }

        $gender = trim((string) ($params['gender'] ?? ''));
        if ($gender !== '') {
            $query->whereHas('profile', fn (Builder $q) => $q->where('gender', $gender));
        }

        if (isset($params['min_age']) && $params['min_age'] !== '') {
            $maxBirthDate = now()->subYears((int) $params['min_age'])->toDateString();
            $query->whereHas('profile', fn (Builder $q) => $q->whereNotNull('date_of_birth')->where('date_of_birth', '<=', $maxBirthDate));
        }

        if (isset($params['max_age']) && $params['max_age'] !== '') {
            $minBirthDate = now()->subYears(((int) $params['max_age']) + 1)->addDay()->toDateString();
            $query->whereHas('profile', fn (Builder $q) => $q->whereNotNull('date_of_birth')->where('date_of_birth', '>=', $minBirthDate));
        }

        if (! empty($params['birth_after'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('date_of_birth', '>=', (string) $params['birth_after']));
        }

        if (! empty($params['birth_before'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('date_of_birth', '<=', (string) $params['birth_before']));
        }

        $maritalStatus = trim((string) ($params['marital_status'] ?? ''));
        if ($maritalStatus !== '') {
            $query->whereHas('profile', fn (Builder $q) => $q->where('marital_status', $maritalStatus));
        }

        if (isset($params['min_children']) && $params['min_children'] !== '') {
            $query->whereHas('profile', fn (Builder $q) => $q->where('number_of_children', '>=', (int) $params['min_children']));
        }

        if (isset($params['max_children']) && $params['max_children'] !== '') {
            $query->whereHas('profile', fn (Builder $q) => $q->where('number_of_children', '<=', (int) $params['max_children']));
        }

        if (! empty($params['nationality_country_id'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('nationality_country_id', (int) $params['nationality_country_id']));
        }

        $residenceCity = trim((string) ($params['residence_city'] ?? ''));
        if ($residenceCity !== '') {
            $like = '%'.$residenceCity.'%';
            $query->whereHas('profile', fn (Builder $q) => $q->where('residence_city', 'like', $like));
        }

        if (! empty($params['agency_id'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('agency_id', (int) $params['agency_id']));
        }

        if (! empty($params['discovery_source_id'])) {
            $query->whereHas('profile', fn (Builder $q) => $q->where('discovery_source_id', (int) $params['discovery_source_id']));
        }

        $this->applyTristate(
            $query,
            $params['has_matricule'] ?? null,
            fn (Builder $q) => $q->whereHas('profile', fn (Builder $p) => $p->whereNotNull('matricule')->where('matricule', '!=', '')),
            fn (Builder $q) => $q->where(function (Builder $inner): void {
                $inner->whereDoesntHave('profile')
                    ->orWhereHas('profile', fn (Builder $p) => $p->where(function (Builder $m): void {
                        $m->whereNull('matricule')->orWhere('matricule', '');
                    }));
            }),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyDomainFilters(Builder $query, array $params): void
    {
        $sectorIds = $this->parseIntList($params['sector_ids'] ?? null);
        if ($sectorIds !== []) {
            $matchAll = ($params['sector_match'] ?? 'any') === 'all';
            if ($matchAll) {
                foreach ($sectorIds as $sectorId) {
                    $query->whereHas('sectors', fn (Builder $q) => $q->where('categories.id', $sectorId));
                }
            } else {
                $query->whereHas('sectors', fn (Builder $q) => $q->whereIn('categories.id', $sectorIds));
            }
        }

        $experienceIndustryIds = $this->parseIntList($params['experience_industry_ids'] ?? null);
        if ($experienceIndustryIds !== []) {
            $query->whereHas('experiences', fn (Builder $q) => $q->whereIn('industry_id', $experienceIndustryIds));
        }

        $jobTitle = trim((string) ($params['experience_job_title'] ?? ''));
        if ($jobTitle !== '') {
            $like = '%'.$jobTitle.'%';
            $query->whereHas('experiences', fn (Builder $q) => $q->where('job_title', 'like', $like));
        }

        $company = trim((string) ($params['experience_company'] ?? ''));
        if ($company !== '') {
            $like = '%'.$company.'%';
            $query->whereHas('experiences', fn (Builder $q) => $q->where('company_name', 'like', $like));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyCareerFilters(Builder $query, array $params): void
    {
        if (isset($params['min_years_experience']) && $params['min_years_experience'] !== '') {
            $min = (int) $params['min_years_experience'];
            $query->whereHas('trades', fn (Builder $q) => $q->where('user_trade.years_of_experience', '>=', $min));
        }

        if (isset($params['max_years_experience']) && $params['max_years_experience'] !== '') {
            $max = (int) $params['max_years_experience'];
            $query->whereDoesntHave('trades', fn (Builder $q) => $q->where('user_trade.years_of_experience', '>', $max));
        }

        if (! empty($params['min_experiences'])) {
            $query->has('experiences', '>=', (int) $params['min_experiences']);
        }

        if (! empty($params['max_experiences'])) {
            $query->has('experiences', '<=', (int) $params['max_experiences']);
        }

        if (! empty($params['experience_country_id'])) {
            $query->whereHas('experiences', fn (Builder $q) => $q->where('country_id', (int) $params['experience_country_id']));
        }

        $experienceStatus = trim((string) ($params['experience_status'] ?? ''));
        if ($experienceStatus !== '') {
            $query->whereHas('experiences', fn (Builder $q) => $q->where('status', $experienceStatus));
        }

        $this->applyTristate(
            $query,
            $params['has_current_job'] ?? null,
            fn (Builder $q) => $q->whereHas('experiences', fn (Builder $e) => $e->where('is_current', true)),
            fn (Builder $q) => $q->whereDoesntHave('experiences', fn (Builder $e) => $e->where('is_current', true)),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyEducationFilters(Builder $query, array $params): void
    {
        if (! empty($params['education_level_id'])) {
            $query->whereHas('educations', fn (Builder $q) => $q->where('education_level_id', (int) $params['education_level_id']));
        }

        $educationField = trim((string) ($params['education_field'] ?? ''));
        if ($educationField !== '') {
            $like = '%'.$educationField.'%';
            $query->whereHas('educations', fn (Builder $q) => $q->where(function (Builder $inner) use ($like): void {
                $inner->where('field_of_study', 'like', $like)
                    ->orWhere('degree', 'like', $like)
                    ->orWhere('institution_name', 'like', $like);
            }));
        }

        if (! empty($params['education_country_id'])) {
            $query->whereHas('educations', fn (Builder $q) => $q->where('country_id', (int) $params['education_country_id']));
        }

        if (! empty($params['min_educations'])) {
            $query->has('educations', '>=', (int) $params['min_educations']);
        }

        if (! empty($params['max_educations'])) {
            $query->has('educations', '<=', (int) $params['max_educations']);
        }

        $educationApproved = $params['education_approved'] ?? null;
        if ($educationApproved !== null && $educationApproved !== '') {
            $approved = filter_var($educationApproved, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($approved !== null) {
                $query->whereHas('educations', fn (Builder $q) => $q->where('is_approved', $approved));
            }
        }

        if (! empty($params['min_certifications'])) {
            $query->has('certifications', '>=', (int) $params['min_certifications']);
        }

        $certificationName = trim((string) ($params['certification_name'] ?? ''));
        if ($certificationName !== '') {
            $like = '%'.$certificationName.'%';
            $query->whereHas('certifications', fn (Builder $q) => $q->where(function (Builder $inner) use ($like): void {
                $inner->where('name', 'like', $like)
                    ->orWhere('issuing_organization', 'like', $like);
            }));
        }

        $certificationStatus = trim((string) ($params['certification_status'] ?? ''));
        if ($certificationStatus !== '') {
            $query->whereHas('certifications', fn (Builder $q) => $q->where('status', $certificationStatus));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applySkillFilters(Builder $query, array $params): void
    {
        if (! empty($params['language_id']) || ! empty($params['language_level_id']) || isset($params['language_approved'])) {
            $languageId = ! empty($params['language_id']) ? (int) $params['language_id'] : null;
            $levelIds = $this->resolveMinimumLanguageLevelIds($params['language_level_id'] ?? null);

            $query->whereHas('languages', function (Builder $q) use ($languageId, $levelIds, $params): void {
                if ($languageId !== null) {
                    $q->where('language_id', $languageId);
                }
                if ($levelIds !== []) {
                    $q->whereIn('language_level_id', $levelIds);
                }
                $languageApproved = $params['language_approved'] ?? null;
                if ($languageApproved !== null && $languageApproved !== '') {
                    $approved = filter_var($languageApproved, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($approved !== null) {
                        $q->where('is_approved', $approved);
                    }
                }
            });
        }

        $skillIds = $this->parseIntList($params['skill_ids'] ?? $params['skill_id'] ?? null);
        if ($skillIds !== []) {
            $query->whereHas('userSkills', function (Builder $q) use ($skillIds, $params): void {
                $q->whereIn('skill_id', $skillIds);

                $skillLevel = trim((string) ($params['skill_level'] ?? ''));
                if ($skillLevel !== '') {
                    $q->where('level', $skillLevel);
                }

                if (isset($params['min_skill_years']) && $params['min_skill_years'] !== '') {
                    $q->where('years_of_experience', '>=', (int) $params['min_skill_years']);
                }
            });
        } elseif (! empty($params['skill_category_id']) || ! empty($params['skill_level']) || (isset($params['min_skill_years']) && $params['min_skill_years'] !== '')) {
            $query->whereHas('userSkills', function (Builder $q) use ($params): void {
                if (! empty($params['skill_category_id'])) {
                    $categoryId = (int) $params['skill_category_id'];
                    $q->whereHas('skill', fn (Builder $skill) => $skill->where('skill_category_id', $categoryId));
                }

                $skillLevel = trim((string) ($params['skill_level'] ?? ''));
                if ($skillLevel !== '') {
                    $q->where('level', $skillLevel);
                }

                if (isset($params['min_skill_years']) && $params['min_skill_years'] !== '') {
                    $q->where('years_of_experience', '>=', (int) $params['min_skill_years']);
                }
            });
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyMobilityFilters(Builder $query, array $params): void
    {
        $preferredCountryIds = $this->parseIntList($params['preferred_country_ids'] ?? $params['preferred_country_id'] ?? null);
        if ($preferredCountryIds !== []) {
            $query->whereHas('preferredCountries', fn (Builder $q) => $q->whereIn('country_id', $preferredCountryIds));
        }

        if (! empty($params['visa_country_id']) || ! empty($params['visa_status'])) {
            $query->whereHas('visaHistories', function (Builder $q) use ($params): void {
                if (! empty($params['visa_country_id'])) {
                    $q->where('country_id', (int) $params['visa_country_id']);
                }
                $visaStatus = trim((string) ($params['visa_status'] ?? ''));
                if ($visaStatus !== '') {
                    $q->where('status', $visaStatus);
                }
            });
        }

        $this->applyTristate(
            $query,
            $params['has_visa_history'] ?? null,
            fn (Builder $q) => $q->has('visaHistories'),
            fn (Builder $q) => $q->doesntHave('visaHistories'),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyDocumentFilters(Builder $query, array $params): void
    {
        if (! empty($params['document_type_id']) || ! empty($params['document_status']) || isset($params['has_valid_documents'])) {
            $query->whereHas('documents', function (Builder $q) use ($params): void {
                if (! empty($params['document_type_id'])) {
                    $q->where('document_type_id', (int) $params['document_type_id']);
                }
                $documentStatus = trim((string) ($params['document_status'] ?? ''));
                if ($documentStatus !== '') {
                    $q->where('status', $documentStatus);
                }
                $hasValid = $params['has_valid_documents'] ?? null;
                if ($hasValid === '1' || $hasValid === 1 || $hasValid === true || $hasValid === 'true') {
                    $q->where(function (Builder $inner): void {
                        $inner->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now()->toDateString());
                    })->where('status', 'APPROVED');
                } elseif ($hasValid === '0' || $hasValid === 0 || $hasValid === false || $hasValid === 'false') {
                    $q->where(function (Builder $inner): void {
                        $inner->where('status', 'EXPIRED')
                            ->orWhere(function (Builder $exp): void {
                                $exp->whereNotNull('expiry_date')
                                    ->where('expiry_date', '<', now()->toDateString());
                            });
                    });
                }
            });
        }

        if (! empty($params['min_documents'])) {
            $query->has('documents', '>=', (int) $params['min_documents']);
        }

        if (! empty($params['max_documents'])) {
            $query->has('documents', '<=', (int) $params['max_documents']);
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyApplicationFilters(Builder $query, array $params): void
    {
        $this->applyTristate(
            $query,
            $params['has_applications'] ?? null,
            fn (Builder $q) => $q->has('applications'),
            fn (Builder $q) => $q->doesntHave('applications'),
        );

        if (! empty($params['min_applications'])) {
            $query->has('applications', '>=', (int) $params['min_applications']);
        }

        if (! empty($params['application_status'])) {
            $statuses = is_array($params['application_status'])
                ? $params['application_status']
                : explode(',', (string) $params['application_status']);
            $statuses = array_values(array_filter(array_map('trim', $statuses)));
            if ($statuses !== []) {
                $query->whereHas('applications', fn (Builder $q) => $q->whereIn('status', $statuses));
            }
        }

        if (! empty($params['category_id'])) {
            $categoryId = (int) $params['category_id'];
            $query->whereHas('applications.offer', fn (Builder $q) => $q->where('category_id', $categoryId));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyInterviewFilters(Builder $query, array $params): void
    {
        $this->applyTristate(
            $query,
            $params['has_interviews'] ?? null,
            fn (Builder $q) => $q->whereHas('applications.interviews'),
            fn (Builder $q) => $q->whereDoesntHave('applications.interviews'),
        );

        if (! empty($params['interview_status']) || ! empty($params['interview_result'])) {
            $query->whereHas('applications.interviews', function (Builder $q) use ($params): void {
                $interviewStatus = trim((string) ($params['interview_status'] ?? ''));
                if ($interviewStatus !== '') {
                    $q->where('status', $interviewStatus);
                }
                $interviewResult = trim((string) ($params['interview_result'] ?? ''));
                if ($interviewResult !== '') {
                    $q->where('result', $interviewResult);
                }
            });
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyTrainingFilters(Builder $query, array $params): void
    {
        if (! empty($params['training_id']) || ! empty($params['training_status'])) {
            $query->whereHas('trainings', function (Builder $q) use ($params): void {
                if (! empty($params['training_id'])) {
                    $q->where('training_id', (int) $params['training_id']);
                }
                $trainingStatus = trim((string) ($params['training_status'] ?? ''));
                if ($trainingStatus !== '') {
                    $q->where('status', $trainingStatus);
                }
            });
        }

        $this->applyTristate(
            $query,
            $params['has_trainings'] ?? null,
            fn (Builder $q) => $q->has('trainings'),
            fn (Builder $q) => $q->doesntHave('trainings'),
        );

        $this->applyTristate(
            $query,
            $params['has_internships'] ?? null,
            fn (Builder $q) => $q->has('internships'),
            fn (Builder $q) => $q->doesntHave('internships'),
        );

        $internshipType = trim((string) ($params['internship_type'] ?? ''));
        if ($internshipType !== '') {
            $query->whereHas('internships', fn (Builder $q) => $q->where('type', $internshipType));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function applyDateFilters(Builder $query, array $params): void
    {
        if (! empty($params['created_after'])) {
            $query->where('created_at', '>=', (string) $params['created_after']);
        }

        if (! empty($params['created_before'])) {
            $query->where('created_at', '<=', (string) $params['created_before'].' 23:59:59');
        }

        if (! empty($params['updated_after'])) {
            $query->where('updated_at', '>=', (string) $params['updated_after']);
        }

        if (! empty($params['updated_before'])) {
            $query->where('updated_at', '<=', (string) $params['updated_before'].' 23:59:59');
        }
    }

    /**
     * @return array<int, int>
     */
    private function resolveMinimumLanguageLevelIds(mixed $languageLevelId): array
    {
        if ($languageLevelId === null || $languageLevelId === '') {
            return [];
        }

        $level = LanguageLevel::query()->find((int) $languageLevelId);
        if ($level === null) {
            return [(int) $languageLevelId];
        }

        return LanguageLevel::query()
            ->where('is_active', true)
            ->where('sort_order', '>=', $level->sort_order)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    private function applyTristate(
        Builder $query,
        mixed $value,
        callable $whenTrue,
        callable $whenFalse,
    ): void {
        if ($value === '1' || $value === 1 || $value === true || $value === 'true') {
            $whenTrue($query);
        } elseif ($value === '0' || $value === 0 || $value === false || $value === 'false') {
            $whenFalse($query);
        }
    }

    /**
     * @return array<int, int>
     */
    private function parseIntList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($v) => (int) $v, $value), static fn (int $v) => $v > 0));
    }
}
