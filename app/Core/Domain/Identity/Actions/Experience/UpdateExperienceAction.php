<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Experience;

use App\Core\Domain\Identity\Models\Experience;

final class UpdateExperienceAction
{
    public function execute(Experience $experience, array $attributes): Experience
    {
        $experience->fill($attributes);
        $experience->save();

        return $experience->fresh(['contractType', 'country', 'document']);
    }
}
