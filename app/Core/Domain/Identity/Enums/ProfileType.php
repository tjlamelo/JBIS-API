<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

use App\Core\Domain\Shared\Support\Concerns\HasEnumLabels;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;

enum ProfileType: string implements LocalizableBackedEnum
{
    use HasEnumLabels;

    case Student = 'student';
    case RecentGraduate = 'recent_graduate';
    case ActiveWorker = 'active_worker';
    case JobSeeker = 'job_seeker';
    case Exploring = 'exploring';

    public static function translationKey(): string
    {
        return 'profile_type';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
