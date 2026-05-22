<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Experience;

use App\Core\Domain\Identity\Models\Experience;

final class DeleteExperienceAction
{
    public function execute(Experience $experience): void
    {
        $experience->delete();
    }
}
