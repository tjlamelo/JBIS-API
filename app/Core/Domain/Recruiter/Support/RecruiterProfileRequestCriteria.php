<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Support;

/**
 * Convertit les critères d'une demande recruteur en paramètres de recherche profils.
 */
final class RecruiterProfileRequestCriteria
{
    public const MAX_QUANTITY = 50;

    /**
     * @param  array<string, mixed>  $criteria
     * @return array<string, mixed>
     */
    public static function toSearchFilters(array $criteria): array
    {
        $filters = [];

        if (! empty($criteria['trade_ids']) && is_array($criteria['trade_ids'])) {
            $filters['trade_ids'] = array_values(array_map('intval', $criteria['trade_ids']));
            $filters['trade_match'] = 'any';
        }

        foreach (['min_years_experience', 'max_years_experience', 'min_age', 'max_age'] as $key) {
            if (isset($criteria[$key]) && $criteria[$key] !== '' && $criteria[$key] !== null) {
                $filters[$key] = (int) $criteria[$key];
            }
        }

        if (! empty($criteria['gender']) && in_array($criteria['gender'], ['M', 'F'], true)) {
            $filters['gender'] = $criteria['gender'];
        }

        if (! empty($criteria['preferred_country_ids']) && is_array($criteria['preferred_country_ids'])) {
            $filters['preferred_country_ids'] = array_values(array_map('intval', $criteria['preferred_country_ids']));
        }

        if (! empty($criteria['language_id'])) {
            $filters['language_id'] = (int) $criteria['language_id'];
        }

        if (! empty($criteria['language_level_id'])) {
            $filters['language_level_id'] = (int) $criteria['language_level_id'];
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'trade_ids' => ['required', 'array', 'min:1'],
            'trade_ids.*' => ['integer', 'exists:trades,id'],
            'min_years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'max_years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'min_age' => ['nullable', 'integer', 'min:16', 'max:80'],
            'max_age' => ['nullable', 'integer', 'min:16', 'max:80'],
            'gender' => ['nullable', 'string', 'in:M,F'],
            'preferred_country_ids' => ['nullable', 'array'],
            'preferred_country_ids.*' => ['integer', 'exists:countries,id'],
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'language_level_id' => ['nullable', 'integer', 'exists:language_levels,id'],
        ];
    }
}
