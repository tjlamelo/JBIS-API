<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportPayload
{
    /**
     * @param  list<ProcessFlowImportFlowData>  $flows
     */
    public function __construct(public array $flows) {}
}
