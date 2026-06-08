<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class InternshipExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'internships';
    }

    public function label(): string
    {
        return 'Stages';
    }

    public function modelClass(): string
    {
        return UserInternship::class;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'internship'),
            $this->field('type', 'Type', group: 'internship'),
            $this->field('title', 'Intitulé', group: 'internship'),
            $this->field('organization', 'Organisation', group: 'internship'),
            $this->field('location', 'Lieu', group: 'internship'),
            $this->field('supervisor_name', 'Responsable', group: 'internship'),
            $this->field('supervisor_contact', 'Contact responsable', group: 'internship'),
            $this->field('start_date', 'Date de début', type: ExportFieldType::Date, format: 'd/m/Y', group: 'internship'),
            $this->field('end_date', 'Date de fin', type: ExportFieldType::Date, format: 'd/m/Y', group: 'internship'),
            $this->field('is_current', 'En cours', type: ExportFieldType::Boolean, group: 'internship'),
            $this->field('description', 'Description', group: 'internship'),
            $this->field('technologies', 'Technologies', group: 'internship'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'internship'),
        ];
    }
}
