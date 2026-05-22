<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\EducationLevel;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\EducationLevel;

final class CreateEducationLevelAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(CatalogNameSlugDto $dto): EducationLevel
    {
        $level = new EducationLevel;
        $this->nameSlugMapper->applyNameAndSlug($level, $dto->provided_keys, $dto->name, $dto->slug, true);
        $level->save();

        return $level->refresh();
    }
}
