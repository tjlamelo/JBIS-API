<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessStep;

use App\Core\Domain\Workflow\Models\ProcessStep;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteProcessStepAction
{
    public function execute(int $processStepId): bool
    {
        /** @var ProcessStep|null $step */
        $step = ProcessStep::query()->find($processStepId);

        if (! $step) {
            throw new ModelNotFoundException("ProcessStep {$processStepId} not found.");
        }

        return (bool) $step->delete();
    }
}
