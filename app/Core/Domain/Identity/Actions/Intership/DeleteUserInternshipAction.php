<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Intership;

use App\Core\Domain\Identity\Models\UserInternship;

final class DeleteUserInternshipAction
{
    public function execute(UserInternship $internship): void
    {
        $internship->delete();
    }
}
