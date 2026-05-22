<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessFlow;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteProcessFlowAction
{
    public function execute(int $processFlowId): bool
    {
        /** @var ProcessFlow|null $flow */
        $flow = ProcessFlow::query()->find($processFlowId);

        if (! $flow) {
            throw new ModelNotFoundException("ProcessFlow {$processFlowId} not found.");
        }

        return (bool) $flow->delete();
    }
}
