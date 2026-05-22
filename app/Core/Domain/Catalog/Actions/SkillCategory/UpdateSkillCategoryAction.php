<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\SkillCategory;

use App\Core\Domain\Catalog\DTOs\CatalogNameSlugDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\SkillCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateSkillCategoryAction
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function execute(int $skillCategoryId, CatalogNameSlugDto $dto): SkillCategory
    {
        /** @var SkillCategory|null $category */
        $category = SkillCategory::query()->find($skillCategoryId);

        if (! $category) {
            throw new ModelNotFoundException("SkillCategory {$skillCategoryId} not found.");
        }

        $this->nameSlugMapper->applyNameAndSlug($category, $dto->provided_keys, $dto->name, $dto->slug, false);
        $category->save();

        return $category->refresh();
    }
}
