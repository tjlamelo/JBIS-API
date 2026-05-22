<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessStep;

use App\Core\Domain\Workflow\DTOs\ProcessStep\ProcessStepDto;
use App\Core\Domain\Workflow\Mappers\ProcessStep\ProcessStepAttributeMapper;
use App\Core\Domain\Workflow\Models\ProcessStep;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateProcessStepAction
{
    public function __construct(
        private readonly ProcessStepAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $processStepId, ProcessStepDto $dto): ProcessStep
    {
        /** @var ProcessStep|null $step */
        $step = ProcessStep::query()->find($processStepId);

        if (! $step) {
            throw new ModelNotFoundException("ProcessStep {$processStepId} not found.");
        }

        $this->attributeMapper->apply($step, $dto, isCreate: false);
        $step->save();

        return $step->refresh();
    }
}
