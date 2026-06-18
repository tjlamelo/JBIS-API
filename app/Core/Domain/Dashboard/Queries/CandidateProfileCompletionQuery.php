<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Identity\Models\UserTraining;

final class CandidateProfileCompletionQuery
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $user->loadCount([
            'experiences',
            'educations',
            'certifications',
            'languages',
            'documents',
            'preferredCountries',
            'visaHistories',
        ]);

        $skillsCount = UserSkill::query()->where('user_id', $user->id)->count();
        $trainingsCount = UserTraining::query()->where('user_id', $user->id)->count();
        $internshipsCount = UserInternship::query()->where('user_id', $user->id)->count();
        $interestsCount = InterestAndHobby::query()->where('user_id', $user->id)->count();

        $profile = $user->profile;

        $sections = [
            'personal' => $this->scorePersonal($profile),
            'contact' => $this->scoreContact($profile),
            'professional' => $this->scoreProfessional($profile),
            'experiences' => $user->experiences_count > 0 ? 100 : 0,
            'education' => $user->educations_count > 0 ? 100 : 0,
            'certifications' => $user->certifications_count > 0 ? 100 : 0,
            'languages' => $user->languages_count > 0 ? 100 : 0,
            'mobility' => ($user->preferred_countries_count > 0 || $user->visa_histories_count > 0) ? 100 : 0,
        ];

        $values = array_values($sections);
        $overall = count($values) > 0
            ? (int) round(array_sum($values) / count($values))
            : 0;

        return [
            'overall_percent' => $overall,
            'sections' => $sections,
            'counts' => [
                'experiences' => $user->experiences_count,
                'educations' => $user->educations_count,
                'certifications' => $user->certifications_count,
                'languages' => $user->languages_count,
                'documents' => $user->documents_count,
                'skills' => $skillsCount,
                'trainings' => $trainingsCount,
                'internships' => $internshipsCount,
                'interests' => $interestsCount,
                'preferred_countries' => $user->preferred_countries_count,
                'visa_histories' => $user->visa_histories_count,
            ],
        ];
    }

    private function scorePersonal(?object $profile): int
    {
        if ($profile === null) {
            return 0;
        }

        $fields = [
            $profile->first_name ?? null,
            $profile->last_name ?? null,
            $profile->date_of_birth ?? null,
            $profile->nationality_country_id ?? null,
            $profile->highest_education_level_id ?? null,
        ];
        $filled = count(array_filter($fields, fn ($v) => $v !== null && $v !== ''));

        return (int) round(($filled / max(1, count($fields))) * 100);
    }

    private function scoreContact(?object $profile): int
    {
        if ($profile === null) {
            return 0;
        }

        $filled = 0;
        if (! empty($profile->address)) {
            $filled++;
        }
        if (! empty($profile->phone_number2) || ! empty($profile->phone_number3)) {
            $filled++;
        }

        return $filled >= 2 ? 100 : ($filled === 1 ? 50 : 0);
    }

    private function scoreProfessional(?object $profile): int
    {
        if ($profile === null) {
            return 0;
        }

        $score = 0;
        if (! empty($profile->bio)) {
            $score += 50;
        }
        if (! empty($profile->agency_id)) {
            $score += 50;
        }

        return min(100, $score);
    }
}
