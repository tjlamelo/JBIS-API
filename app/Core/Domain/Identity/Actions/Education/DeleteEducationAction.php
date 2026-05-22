<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Education;

use App\Core\Domain\Identity\Models\Education;

final class DeleteEducationAction
{
    public function execute(Education $education): void
    {
        $education->delete();
    }
}
