<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Program;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class DeleteProgramAction
{
    public function __construct(
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    public function execute(int $programId): bool
    {
        /** @var Program|null $program */
        $program = Program::query()->find($programId);

        if (! $program) {
            throw new ModelNotFoundException("Program {$programId} not found.");
        }

        $deleted = (bool) $program->delete();

        if ($deleted) {
            $this->catalogCache->invalidate();
        }

        return $deleted;
    }
}
