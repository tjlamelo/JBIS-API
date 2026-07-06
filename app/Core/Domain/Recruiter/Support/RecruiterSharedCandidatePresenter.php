<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Support;

use App\Core\Application\Api\V1\Document\Resources\UserDocumentResource;
use App\Core\Application\Api\V1\Identity\Resources\EducationResource;
use App\Core\Application\Api\V1\Identity\Resources\ExperienceResource;
use App\Core\Application\Api\V1\Identity\Support\ProfileResponseMapper;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;

final class RecruiterSharedCandidatePresenter
{
    public function __construct(
        private readonly ProfileResponseMapper $profileMapper,
    ) {}

    /**
     * @param  list<string>  $visibleSections
     * @return array<string, mixed>
     */
    public function present(User $candidate, array $visibleSections): array
    {
        $sections = RecruiterSharedProfileSection::normalize($visibleSections);
        $allowed = array_flip($sections);

        $data = [
            'id' => $candidate->id,
            'name' => $candidate->name,
        ];

        if (isset($allowed[RecruiterSharedProfileSection::Contact->value])) {
            $data['email'] = $candidate->email;
            $data['phone_number1'] = $candidate->phone_number1;
        }

        if (isset($allowed[RecruiterSharedProfileSection::Profile->value])
            && $candidate->relationLoaded('profile')
            && $candidate->profile !== null) {
            $data['profile'] = $this->profileMapper->toArray($candidate->profile);
        }

        if (isset($allowed[RecruiterSharedProfileSection::Professional->value])) {
            if ($candidate->relationLoaded('trades')) {
                $data['sectors'] = $candidate->sectorsFromTrades()->map(static fn ($sector) => [
                    'id' => $sector->id,
                    'slug' => $sector->slug,
                    'name' => $sector->getTranslations('name'),
                ])->values()->all();
                $data['trades'] = $candidate->trades->map(static fn ($trade) => [
                    'id' => $trade->id,
                    'slug' => $trade->slug,
                    'name' => $trade->getTranslations('name'),
                    'years_of_experience' => $trade->pivot?->years_of_experience,
                ])->values()->all();
            }
        }

        if (isset($allowed[RecruiterSharedProfileSection::Educations->value])
            && $candidate->relationLoaded('educations')) {
            $data['educations'] = EducationResource::collection($candidate->educations)->resolve();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Experiences->value])
            && $candidate->relationLoaded('experiences')) {
            $data['experiences'] = ExperienceResource::collection($candidate->experiences)->resolve();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Languages->value])
            && $candidate->relationLoaded('languages')) {
            $data['languages'] = $candidate->languages->map(static fn ($language) => [
                'id' => $language->id,
                'language' => $language->relationLoaded('language') && $language->language !== null
                    ? $language->language->getTranslations('name')
                    : null,
                'level' => $language->relationLoaded('languageLevel') && $language->languageLevel !== null
                    ? $language->languageLevel->getTranslations('name')
                    : null,
            ])->values()->all();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Skills->value])
            && $candidate->relationLoaded('userSkills')) {
            $data['skills'] = $candidate->userSkills->map(static fn ($userSkill) => [
                'id' => $userSkill->id,
                'skill' => $userSkill->relationLoaded('skill') && $userSkill->skill !== null
                    ? $userSkill->skill->getTranslations('name')
                    : null,
                'level' => $userSkill->level,
            ])->values()->all();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Certifications->value])
            && $candidate->relationLoaded('certifications')) {
            $data['certifications'] = $candidate->certifications->map(static fn ($certification) => [
                'id' => $certification->id,
                'title' => $certification->title,
                'issuer' => $certification->issuer,
                'issued_at' => $certification->issued_at?->toDateString(),
                'expires_at' => $certification->expires_at?->toDateString(),
            ])->values()->all();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Internships->value])
            && $candidate->relationLoaded('internships')) {
            $data['internships'] = $candidate->internships->map(static fn ($internship) => [
                'id' => $internship->id,
                'title' => $internship->title,
                'organization' => $internship->organization,
                'started_at' => $internship->started_at?->toDateString(),
                'ended_at' => $internship->ended_at?->toDateString(),
                'description' => $internship->description,
            ])->values()->all();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Interests->value])
            && $candidate->relationLoaded('interests')) {
            $data['interests'] = $candidate->interests->map(static fn ($interest) => [
                'id' => $interest->id,
                'name' => $interest->name,
                'description' => $interest->description,
            ])->values()->all();
        }

        if (isset($allowed[RecruiterSharedProfileSection::Documents->value])
            && $candidate->relationLoaded('documents')) {
            $data['documents'] = UserDocumentResource::collection($candidate->documents)->resolve();
        }

        return $data;
    }

    /**
     * @param  list<string>  $visibleSections
     * @return list<string>
     */
    public function eagerLoadsFor(array $visibleSections): array
    {
        $sections = RecruiterSharedProfileSection::normalize($visibleSections);
        $allowed = array_flip($sections);
        $loads = [];

        if (isset($allowed[RecruiterSharedProfileSection::Profile->value])) {
            $loads[] = 'profile';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Professional->value])) {
            $loads[] = 'trades.category';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Educations->value])) {
            $loads[] = 'educations.level';
            $loads[] = 'educations.country';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Experiences->value])) {
            $loads[] = 'experiences.contractType';
            $loads[] = 'experiences.country';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Languages->value])) {
            $loads[] = 'languages.language';
            $loads[] = 'languages.languageLevel';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Skills->value])) {
            $loads[] = 'userSkills.skill';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Certifications->value])) {
            $loads[] = 'certifications';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Internships->value])) {
            $loads[] = 'internships';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Interests->value])) {
            $loads[] = 'interests';
        }

        if (isset($allowed[RecruiterSharedProfileSection::Documents->value])) {
            $loads[] = 'documents.documentType';
        }

        return $loads;
    }
}
