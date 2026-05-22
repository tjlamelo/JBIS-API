<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Education;

use App\Core\Domain\Identity\Models\Education;

final class UpdateEducationAction
{
    public function execute(Education $education, array $attributes): Education
    {
        $education->fill($attributes);
        $education->save();

        return $education->fresh(['level', 'country', 'document']);
    }
}
