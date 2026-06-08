<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Program;

use App\Core\Domain\Catalog\DTOs\Program\ProgramDto;
use App\Core\Domain\Catalog\Mappers\Program\ProgramAttributeMapper;
use App\Core\Domain\Catalog\Mappers\Program\ProgramRelationSync;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateProgramAction
{
    public function __construct(
        private readonly ProgramAttributeMapper $attributeMapper,
        private readonly ProgramRelationSync $relationSync,
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    public function execute(int $programId, ProgramDto $dto): Program
    {
        /** @var Program|null $program */
        $program = Program::query()->find($programId);

        if (! $program) {
            throw new ModelNotFoundException("Program {$programId} not found.");
        }

        $this->attributeMapper->apply($program, $dto, isCreate: false);
        $program->save();

        if ($dto->has('required_documents')) {
            $this->relationSync->syncRequiredDocuments($program, $dto->required_documents);
        }

        if ($dto->has('language_requirements')) {
            $this->relationSync->syncLanguages($program, $dto->language_requirements);
        }

        $program = $program->refresh();
        $this->catalogCache->invalidate();

        return $program;
    }
}
