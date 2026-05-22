<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Actions\ProcessFlow;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Publie une nouvelle version : clone en draft puis publie (archive l'ancienne version published).
 */
final class PublishProcessFlowVersionAction
{
    public function execute(int $processFlowId): ProcessFlow
    {
        /** @var ProcessFlow|null $current */
        $current = ProcessFlow::query()
            ->with(['sections.steps', 'steps'])
            ->find($processFlowId);

        if (! $current) {
            throw new ModelNotFoundException("ProcessFlow {$processFlowId} not found.");
        }

        $draft = $current->status->value === 'draft'
            ? $current
            : $current->cloneAsNewVersion();

        return $draft->publish()->load(['sections.steps', 'steps']);
    }
}
