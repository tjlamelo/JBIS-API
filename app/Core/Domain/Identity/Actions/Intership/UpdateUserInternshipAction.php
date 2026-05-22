<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Intership;

use App\Core\Domain\Identity\Models\UserInternship;

final class UpdateUserInternshipAction
{
    public function execute(UserInternship $internship, array $attributes): UserInternship
    {
        $internship->fill($attributes);
        $internship->save();

        return $internship->fresh(['certificateDocument']);
    }
}
