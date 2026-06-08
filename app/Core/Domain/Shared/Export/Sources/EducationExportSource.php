<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class EducationExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'educations';
    }

    public function label(): string
    {
        return 'Formations & diplômes';
    }

    public function modelClass(): string
    {
        return Education::class;
    }

    public function defaultWith(): array
    {
        return ['level', 'country'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'education'),
            $this->field('degree', 'Diplôme', group: 'education'),
            $this->field('institution_name', 'Établissement', group: 'education'),
            $this->field('field_of_study', 'Domaine d\'études', group: 'education'),
            $this->field('level.name', 'Niveau', type: ExportFieldType::Translatable, group: 'education', requiresWith: ['level']),
            $this->field('country.name', 'Pays', type: ExportFieldType::Translatable, group: 'education', requiresWith: ['country']),
            $this->field('start_date', 'Date de début', type: ExportFieldType::Date, format: 'd/m/Y', group: 'education'),
            $this->field('end_date', 'Date de fin', type: ExportFieldType::Date, format: 'd/m/Y', group: 'education'),
            $this->field('is_current', 'En cours', type: ExportFieldType::Boolean, group: 'education'),
            $this->field('grade', 'Mention / note', group: 'education'),
            $this->field('is_approved', 'Approuvé', type: ExportFieldType::Boolean, group: 'education'),
        ];
    }
}
