<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowDto;
use App\Core\Domain\Workflow\Mappers\ProcessFlow\ProcessFlowAttributeMapper;
use App\Core\Domain\Workflow\Mappers\ProcessFlow\ProcessFlowSectionsSync;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowFeeRecalculator;

final class CreateProcessFlowAction
{
    public function __construct(
        private readonly ProcessFlowAttributeMapper $attributeMapper,
        private readonly ProcessFlowSectionsSync $sectionsSync,
        private readonly ProcessFlowFeeRecalculator $feeRecalculator,
    ) {}

    public function execute(ProcessFlowDto $dto): ProcessFlow
    {
        $flow = new ProcessFlow;
        $this->attributeMapper->apply($flow, $dto, isCreate: true);
        $flow->save();

        if ($dto->sections !== []) {
            $this->sectionsSync->sync($flow, $dto->sections);
        }

        $this->feeRecalculator->recalculate($flow);

        return $flow->load(['sections.steps', 'steps']);
    }
}
