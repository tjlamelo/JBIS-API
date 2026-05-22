<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Skill;

use App\Core\Domain\Catalog\DTOs\Skill\SkillDto;
use App\Core\Domain\Catalog\Mappers\Shared\CatalogTranslatableNameSlugMapper;
use App\Core\Domain\Catalog\Models\Skill;

final class SkillAttributeMapper
{
    public function __construct(
        private readonly CatalogTranslatableNameSlugMapper $nameSlugMapper,
    ) {}

    public function apply(Skill $skill, SkillDto $dto, bool $isCreate): void
    {
        $this->nameSlugMapper->applyNameAndSlug(
            $skill,
            $dto->provided_keys,
            $dto->name,
            $dto->slug,
            $isCreate,
        );

        if ($isCreate || $dto->has('skill_category_id')) {
            $skill->skill_category_id = $dto->skill_category_id;
        }
    }
}
