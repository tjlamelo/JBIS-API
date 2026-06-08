<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Candidacy\Models\Appointment;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class AppointmentExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'appointments';
    }

    public function label(): string
    {
        return 'Rendez-vous';
    }

    public function modelClass(): string
    {
        return Appointment::class;
    }

    public function defaultWith(): array
    {
        return ['agency'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'appointment'),
            $this->field('full_name', 'Nom', group: 'appointment'),
            $this->field('email', 'Email', group: 'appointment'),
            $this->field('phone', 'Téléphone', group: 'appointment'),
            $this->field('scheduled_at', 'Planifié le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'appointment'),
            $this->field('duration_minutes', 'Durée (min)', type: ExportFieldType::Integer, group: 'appointment'),
            $this->field('subject', 'Sujet', group: 'appointment'),
            $this->field('type', 'Type', type: ExportFieldType::Enum, group: 'appointment'),
            $this->field('meeting_link', 'Lien visio', group: 'appointment'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'appointment'),
            $this->field('internal_notes', 'Notes internes', group: 'appointment'),
            $this->field('agency.name', 'Agence', group: 'appointment', requiresWith: ['agency']),
        ];
    }
}
