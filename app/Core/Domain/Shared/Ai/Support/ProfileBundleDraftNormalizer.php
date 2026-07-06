<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

/**
 * Normalise les brouillons IA lorsque le modèle renvoie un JSON plat (json_object)
 * au lieu du schéma ProfileBundle imbriqué.
 */
final class ProfileBundleDraftNormalizer
{
    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    public static function normalize(array $draft): array
    {
        $profile = is_array($draft['user_profile'] ?? null) ? $draft['user_profile'] : [];

        $flatProfileMap = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'full_name' => 'full_name',
            'name' => 'full_name',
            'date_of_birth' => 'date_of_birth',
            'place_of_birth' => 'place_of_birth',
            'nationality_country_name' => 'nationality_country_name',
            'nationality' => 'nationality_country_name',
            'residence_city_name' => 'residence_city_name',
            'city' => 'residence_city_name',
            'address' => 'address',
            'phone_number2' => 'phone_number2',
            'phone' => 'phone_number2',
            'phone_number3' => 'phone_number3',
            'gender' => 'gender',
            'bio' => 'bio',
            'summary' => 'bio',
            'profile_summary' => 'bio',
            'professional_summary' => 'bio',
            'about' => 'bio',
            'about_me' => 'bio',
            'presentation' => 'bio',
            'profil' => 'bio',
            'objectif' => 'bio',
            'objectif_professionnel' => 'bio',
            'career_summary' => 'bio',
            'personal_statement' => 'bio',
            'a_propos' => 'bio',
            'resume_professionnel' => 'bio',
            'marital_status' => 'marital_status',
            'number_of_children' => 'number_of_children',
            'email_institutional' => 'email_institutional',
            'email' => 'email_institutional',
            'situation_matrimoniale' => 'marital_status',
            'etat_civil' => 'marital_status',
            'marital_status_label' => 'marital_status',
        ];

        foreach ($flatProfileMap as $sourceKey => $targetKey) {
            if (! array_key_exists($sourceKey, $draft)) {
                continue;
            }
            $value = $draft[$sourceKey];
            if ($value === null || $value === '') {
                continue;
            }
            if (! isset($profile[$targetKey]) || $profile[$targetKey] === '' || $profile[$targetKey] === null) {
                $profile[$targetKey] = $value;
            }
        }

        if ($profile !== []) {
            $draft['user_profile'] = $profile;
        }

        foreach (['educations', 'experiences', 'certifications', 'languages', 'formations', 'internships', 'skills', 'interests'] as $listKey) {
            if (! isset($draft[$listKey])) {
                $draft[$listKey] = [];

                continue;
            }

            if (in_array($listKey, ['skills', 'interests'], true) && is_string($draft[$listKey])) {
                continue;
            }

            if (! is_array($draft[$listKey])) {
                $draft[$listKey] = [];
            }
        }

        $collected = InterestsDraftCollector::collect($draft);
        if ($collected !== []) {
            $draft['interests'] = $collected;
        }

        if (! array_key_exists('notes', $draft)) {
            $draft['notes'] = '';
        }

        return $draft;
    }
}
