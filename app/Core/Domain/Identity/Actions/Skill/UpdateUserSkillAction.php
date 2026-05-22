<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Skill;

use App\Core\Domain\Identity\Models\UserSkill;

final class UpdateUserSkillAction
{
    public function execute(UserSkill $userSkill, array $attributes): UserSkill
    {
        $userSkill->fill($attributes);
        $userSkill->save();

        return $userSkill->fresh(['skill']);
    }
}
