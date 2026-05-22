<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\SkillCategory;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\SkillCategory;

final class CreateSkillCategoryAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(CatalogNameSlugDto $dto): SkillCategory
    {
        $category = new SkillCategory;
        $this->nameSlugMapper->applyNameAndSlug($category, $dto->provided_keys, $dto->name, $dto->slug, true);
        $category->save();

        return $category->refresh();
    }
}
