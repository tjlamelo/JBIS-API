<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\States;

use App\Core\Domain\Shared\Support\Concerns\HasEnumLabels;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;

enum LanguageCourseStatus: string implements LocalizableBackedEnum
{
    use HasEnumLabels;

    case Planned = 'PLANNED';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Deferred = 'DEFERRED';
    case Failed = 'FAILED';

    public static function translationKey(): string
    {
        return 'language_course_status';
    }
}
