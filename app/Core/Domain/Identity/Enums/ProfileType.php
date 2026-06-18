<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

enum ProfileType: string
{
    case Student = 'student';
    case RecentGraduate = 'recent_graduate';
    case ActiveWorker = 'active_worker';
    case JobSeeker = 'job_seeker';
    case Exploring = 'exploring';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
