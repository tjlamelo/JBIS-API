<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowDto;
use App\Core\Domain\Workflow\Mappers\ProcessFlow\ProcessFlowAttributeMapper;
use App\Core\Domain\Workflow\Mappers\ProcessFlow\ProcessFlowSectionsSync;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowFeeRecalculator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateProcessFlowAction
{
    public function __construct(
        private readonly ProcessFlowAttributeMapper $attributeMapper,
        private readonly ProcessFlowSectionsSync $sectionsSync,
        private readonly ProcessFlowFeeRecalculator $feeRecalculator,
    ) {}

    public function execute(int $processFlowId, ProcessFlowDto $dto): ProcessFlow
    {
        /** @var ProcessFlow|null $flow */
        $flow = ProcessFlow::query()->find($processFlowId);

        if (! $flow) {
            throw new ModelNotFoundException("ProcessFlow {$processFlowId} not found.");
        }

        $this->attributeMapper->apply($flow, $dto, isCreate: false);
        $flow->save();

        if ($dto->has('sections')) {
            $this->sectionsSync->sync($flow, $dto->sections);
        }

        $this->feeRecalculator->recalculate($flow);

        return $flow->load(['sections.steps', 'steps']);
    }
}
