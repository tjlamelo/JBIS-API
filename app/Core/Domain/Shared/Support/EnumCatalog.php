<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Support;

use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\LanguageCourseStatus;
use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\ProfileType;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;
use InvalidArgumentException;

final class EnumCatalog
{
    /** @var array<string, class-string<LocalizableBackedEnum>> */
    public const GROUPS = [
        'profile_type' => ProfileType::class,
        'career_intent' => CareerIntent::class,
        'application_status' => ApplicationStatus::class,
        'language_course_status' => LanguageCourseStatus::class,
    ];

    /**
     * @param  list<string>  $keys
     * @return array<string, list<array<string, mixed>>>
     */
    public function resolve(array $keys, bool $bilingual = true): array
    {
        $result = [];

        foreach ($keys as $key) {
            $enumClass = self::GROUPS[$key] ?? null;
            if ($enumClass === null) {
                continue;
            }

            $result[$key] = $bilingual
                ? $enumClass::bilingualOptions()
                : $enumClass::options(app()->getLocale());
        }

        return $result;
    }

    /**
     * @return class-string<LocalizableBackedEnum>
     */
    public static function classFor(string $key): string
    {
        $enumClass = self::GROUPS[$key] ?? null;
        if ($enumClass === null) {
            throw new InvalidArgumentException("Unknown enum group [{$key}].");
        }

        return $enumClass;
    }
}
