<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportContext
{
    public function __construct(public ?int $importedByUserId = null) {}
}
