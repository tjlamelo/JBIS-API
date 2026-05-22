<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Skill;

use App\Core\Domain\Identity\Models\UserSkill;

final class DeleteUserSkillAction
{
    public function execute(UserSkill $userSkill): void
    {
        $userSkill->delete();
    }
}
