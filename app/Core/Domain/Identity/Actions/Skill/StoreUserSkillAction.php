<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Skill;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSkill;

final class StoreUserSkillAction
{
    public function execute(User $user, array $attributes): UserSkill
    {
        return UserSkill::query()->create([
            ...$attributes,
            'user_id' => $user->id,
        ]);
    }
}
