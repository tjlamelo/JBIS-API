<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Training;

use App\Core\Domain\Identity\Models\UserTraining;

final class DeleteUserTrainingAction
{
    public function execute(UserTraining $userTraining): void
    {
        $userTraining->delete();
    }
}
