<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Program;

use App\Core\Domain\Catalog\DTOs\Program\ProgramDto;
use App\Core\Domain\Catalog\Mappers\Program\ProgramAttributeMapper;
use App\Core\Domain\Catalog\Mappers\Program\ProgramRelationSync;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;

final class CreateProgramAction
{
    public function __construct(
        private readonly ProgramAttributeMapper $attributeMapper,
        private readonly ProgramRelationSync $relationSync,
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    public function execute(ProgramDto $dto): Program
    {
        $program = new Program;
        $this->attributeMapper->apply($program, $dto, isCreate: true);
        $program->save();

        $this->relationSync->syncRequiredDocuments($program, $dto->required_documents);

        if ($dto->language_requirements !== []) {
            $this->relationSync->syncLanguages($program, $dto->language_requirements);
        }

        $program = $program->refresh();
        $this->catalogCache->invalidate();

        return $program;
    }
}
