<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Skill;

use App\Core\Domain\Catalog\DTOs\Skill\SkillDto;
use App\Core\Domain\Catalog\Mappers\Skill\SkillAttributeMapper;
use App\Core\Domain\Catalog\Models\Skill;

final class CreateSkillAction
{
    public function __construct(
        private readonly SkillAttributeMapper $attributeMapper,
    ) {}

    public function execute(SkillDto $dto): Skill
    {
        $skill = new Skill;
        $this->attributeMapper->apply($skill, $dto, isCreate: true);
        $skill->save();

        return $skill->refresh();
    }
}
