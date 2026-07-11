<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterMaskedField: string
{
    case ContactEmail = 'contact_email';
    case ContactPhone = 'contact_phone';
    case ProfilePhones = 'profile_phones';
    case ProfileAddress = 'profile_address';
    case ProfileEmailInstitutional = 'profile_email_institutional';
    case ProfileMatricule = 'profile_matricule';
    case ProfilePictures = 'profile_pictures';
    case ProfileAgency = 'profile_agency';
    case ProfileDiscovery = 'profile_discovery';
    case ProfilePlaceOfBirth = 'profile_place_of_birth';
    case ProfileMaritalInfo = 'profile_marital_info';
    case ProfileDateOfBirth = 'profile_date_of_birth';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @param  list<string>|null  $fields
     * @return list<string>
     */
    public static function normalize(?array $fields): array
    {
        if ($fields === null || $fields === []) {
            return [];
        }

        $allowed = array_flip(self::values());

        return array_values(array_filter(
            $fields,
            static fn (string $field): bool => isset($allowed[$field]),
        ));
    }
}
