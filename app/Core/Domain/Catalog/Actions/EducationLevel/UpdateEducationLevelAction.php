<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\EducationLevel;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\EducationLevel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateEducationLevelAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(int $educationLevelId, CatalogNameSlugDto $dto): EducationLevel
    {
        /** @var EducationLevel|null $level */
        $level = EducationLevel::query()->find($educationLevelId);

        if (! $level) {
            throw new ModelNotFoundException("EducationLevel {$educationLevelId} not found.");
        }

        $this->nameSlugMapper->applyNameAndSlug($level, $dto->provided_keys, $dto->name, $dto->slug, false);
        $level->save();

        return $level->refresh();
    }
}
