<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Skill;

use App\Core\Domain\Catalog\Models\Skill;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteSkillAction
{
    public function execute(int $skillId): bool
    {
        /** @var Skill|null $skill */
        $skill = Skill::query()->find($skillId);

        if (! $skill) {
            throw new ModelNotFoundException("Skill {$skillId} not found.");
        }

        return (bool) $skill->delete();
    }
}
