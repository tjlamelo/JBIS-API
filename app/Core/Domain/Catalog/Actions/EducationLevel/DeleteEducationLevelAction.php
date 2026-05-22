<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\EducationLevel;

use App\Core\Domain\Catalog\Models\EducationLevel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteEducationLevelAction
{
    public function execute(int $educationLevelId): bool
    {
        /** @var EducationLevel|null $level */
        $level = EducationLevel::query()->find($educationLevelId);

        if (! $level) {
            throw new ModelNotFoundException("EducationLevel {$educationLevelId} not found.");
        }

        return (bool) $level->delete();
    }
}
