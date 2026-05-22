<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Skill;

use App\Core\Domain\Catalog\DTOs\Skill\SkillDto;
use App\Core\Domain\Catalog\Mappers\Skill\SkillAttributeMapper;
use App\Core\Domain\Catalog\Models\Skill;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateSkillAction
{
    public function __construct(
        private readonly SkillAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $skillId, SkillDto $dto): Skill
    {
        /** @var Skill|null $skill */
        $skill = Skill::query()->find($skillId);

        if (! $skill) {
            throw new ModelNotFoundException("Skill {$skillId} not found.");
        }

        $this->attributeMapper->apply($skill, $dto, isCreate: false);
        $skill->save();

        return $skill->refresh();
    }
}
