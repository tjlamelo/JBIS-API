<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Training;

use App\Core\Domain\Catalog\Models\Training;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteTrainingAction
{
    public function execute(int $trainingId): bool
    {
        /** @var Training|null $training */
        $training = Training::query()->find($trainingId);

        if (! $training) {
            throw new ModelNotFoundException("Training {$trainingId} not found.");
        }

        return (bool) $training->delete();
    }
}
