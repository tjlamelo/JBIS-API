<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class ExperienceExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'experiences';
    }

    public function label(): string
    {
        return 'Expériences professionnelles';
    }

    public function modelClass(): string
    {
        return Experience::class;
    }

    public function defaultWith(): array
    {
        return ['contractType', 'country'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'experience'),
            $this->field('job_title', 'Intitulé du poste', group: 'experience'),
            $this->field('company_name', 'Entreprise', group: 'experience'),
            $this->field('city_name', 'Ville', group: 'experience'),
            $this->field('country.name', 'Pays', type: ExportFieldType::Translatable, group: 'experience', requiresWith: ['country']),
            $this->field('contractType.name', 'Type de contrat', type: ExportFieldType::Translatable, group: 'experience', requiresWith: ['contractType']),
            $this->field('start_date', 'Date de début', type: ExportFieldType::Date, format: 'd/m/Y', group: 'experience'),
            $this->field('end_date', 'Date de fin', type: ExportFieldType::Date, format: 'd/m/Y', group: 'experience'),
            $this->field('is_current', 'En cours', type: ExportFieldType::Boolean, group: 'experience'),
            $this->field('responsibilities', 'Responsabilités', group: 'experience'),
            $this->field('achievements', 'Réalisations', group: 'experience'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'experience'),
            $this->field('approved_at', 'Approuvé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'experience'),
        ];
    }
}
