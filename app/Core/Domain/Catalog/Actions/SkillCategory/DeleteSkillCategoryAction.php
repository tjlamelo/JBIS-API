<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\SkillCategory;

use App\Core\Domain\Catalog\Models\SkillCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteSkillCategoryAction
{
    public function execute(int $skillCategoryId): bool
    {
        /** @var SkillCategory|null $category */
        $category = SkillCategory::query()->find($skillCategoryId);

        if (! $category) {
            throw new ModelNotFoundException("SkillCategory {$skillCategoryId} not found.");
        }

        return (bool) $category->delete();
    }
}
