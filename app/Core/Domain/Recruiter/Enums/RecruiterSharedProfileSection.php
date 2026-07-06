<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterSharedProfileSection: string
{
    case Profile = 'profile';
    case Contact = 'contact';
    case Professional = 'professional';
    case Educations = 'educations';
    case Experiences = 'experiences';
    case Languages = 'languages';
    case Skills = 'skills';
    case Certifications = 'certifications';
    case Internships = 'internships';
    case Interests = 'interests';
    case Documents = 'documents';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @param  list<string>|null  $sections
     * @return list<string>
     */
    public static function normalize(?array $sections): array
    {
        if ($sections === null || $sections === []) {
            return self::values();
        }

        $allowed = array_flip(self::values());

        return array_values(array_filter(
            $sections,
            static fn (string $section): bool => isset($allowed[$section]),
        ));
    }
}
