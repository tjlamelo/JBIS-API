<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserSkillExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_skills';
    }

    public function label(): string
    {
        return 'Compétences';
    }

    public function modelClass(): string
    {
        return UserSkill::class;
    }

    public function defaultWith(): array
    {
        return ['skill'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'skill'),
            $this->field('skill.name', 'Compétence', type: ExportFieldType::Translatable, group: 'skill', requiresWith: ['skill']),
            $this->field('level', 'Niveau', group: 'skill'),
            $this->field('years_of_experience', 'Années d\'expérience', type: ExportFieldType::Integer, group: 'skill'),
        ];
    }
}
