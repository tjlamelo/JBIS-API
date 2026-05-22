<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Program;

use App\Core\Domain\Catalog\Models\Program;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteProgramAction
{
    public function execute(int $programId): bool
    {
        /** @var Program|null $program */
        $program = Program::query()->find($programId);

        if (! $program) {
            throw new ModelNotFoundException("Program {$programId} not found.");
        }

        return (bool) $program->delete();
    }
}
