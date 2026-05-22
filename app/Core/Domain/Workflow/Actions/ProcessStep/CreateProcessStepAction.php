<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessStep;

use App\Core\Domain\Workflow\DTOs\ProcessStep\ProcessStepDto;
use App\Core\Domain\Workflow\Mappers\ProcessStep\ProcessStepAttributeMapper;
use App\Core\Domain\Workflow\Models\ProcessStep;

final class CreateProcessStepAction
{
    public function __construct(
        private readonly ProcessStepAttributeMapper $attributeMapper,
    ) {}

    public function execute(ProcessStepDto $dto): ProcessStep
    {
        $step = new ProcessStep;
        $this->attributeMapper->apply($step, $dto, isCreate: true);
        $step->save();

        return $step->refresh();
    }
}
