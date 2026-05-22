<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Training;

use App\Core\Domain\Identity\Models\UserTraining;

final class UpdateUserTrainingAction
{
    public function execute(UserTraining $userTraining, array $attributes): UserTraining
    {
        $userTraining->fill($attributes);
        $userTraining->save();

        return $userTraining->fresh();
    }
}
