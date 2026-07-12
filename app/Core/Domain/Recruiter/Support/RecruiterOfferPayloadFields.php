<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Support;

/**
 * Champs d’offre qu’un recruteur peut fournir dans une soumission.
 *
 * @phpstan-type OfferPayload array<string, mixed>
 */
final class RecruiterOfferPayloadFields
{
    /** @var list<string> */
    public const RECRUITER_KEYS = [
        'trade_id',
        'description',
        'company_id',
        'contract_type_id',
        'country_id',
        'city_id',
        'salary_min',
        'salary_max',
        'work_mode',
        'skill_requirements',
        'proposed_skills',
    ];

    /**
     * Champs réservés au staff (complétion avant publication), en plus des clés recruteur.
     *
     * @var list<string>
     */
    public const STAFF_EXTRA_KEYS = [
        'address',
        'currency',
        'is_salary_public',
        'is_company_public',
        'allows_public_applications',
        'available_positions',
        'offer_type_id',
        'work_schedule_id',
        'education_level_id',
        'program_id',
        'benefit_ids',
        'language_requirements',
        'skill_requirements',
        'required_documents',
        'expiration_date',
        'published_at',
        'meta',
        'photo',
        'photo_media',
        'slug',
        'language',
    ];

    /**
     * @return list<string>
     */
    public static function staffAllowedKeys(): array
    {
        return array_values(array_unique([...self::RECRUITER_KEYS, ...self::STAFF_EXTRA_KEYS]));
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $allowedKeys
     * @return array<string, mixed>
     */
    public static function only(array $input, array $allowedKeys): array
    {
        return array_intersect_key($input, array_flip($allowedKeys));
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    public static function merge(array $base, array $overlay): array
    {
        $merged = array_merge($base, $overlay);

        if (isset($base['description']) || isset($overlay['description'])) {
            $baseDesc = is_array($base['description'] ?? null) ? $base['description'] : [];
            $overlayDesc = is_array($overlay['description'] ?? null) ? $overlay['description'] : [];
            $merged['description'] = array_merge($baseDesc, $overlayDesc);
        }

        return $merged;
    }

    /**
     * Retire les clés non persistables sur une offre catalogue.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function forOfferCreation(array $payload): array
    {
        unset($payload['proposed_skills']);

        return $payload;
    }
}
