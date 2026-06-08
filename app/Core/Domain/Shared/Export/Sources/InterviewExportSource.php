<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Candidacy\Models\Interview;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class InterviewExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'interviews';
    }

    public function label(): string
    {
        return 'Entretiens';
    }

    public function modelClass(): string
    {
        return Interview::class;
    }

    public function defaultWith(): array
    {
        return ['application.offer', 'application.program', 'company'];
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $userId = (int) $filters['user_id'];
            $query->whereHas('application', fn (Builder $q) => $q->where('user_id', $userId));
        }

        if (! empty($filters['application_id'])) {
            $query->where('application_id', (int) $filters['application_id']);
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'interview'),
            $this->field('application.application_number', 'N° candidature', group: 'interview', requiresWith: ['application']),
            $this->field('application.status', 'Statut candidature', type: ExportFieldType::Enum, group: 'interview', requiresWith: ['application']),
            $this->field('application.offer.title', 'Offre', type: ExportFieldType::Translatable, group: 'interview', requiresWith: ['application.offer']),
            $this->field('application.program.name', 'Programme', type: ExportFieldType::Translatable, group: 'interview', requiresWith: ['application.program']),
            $this->field('scheduled_date', 'Date planifiée', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'interview'),
            $this->field('duration', 'Durée (min)', type: ExportFieldType::Integer, group: 'interview'),
            $this->field('interview_type', 'Type', type: ExportFieldType::Enum, group: 'interview'),
            $this->field('location', 'Lieu', group: 'interview'),
            $this->field('interviewer_name', 'Intervieweur', group: 'interview'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'interview'),
            $this->field('result', 'Résultat', type: ExportFieldType::Enum, group: 'interview'),
            $this->field('company.name', 'Entreprise', group: 'interview', requiresWith: ['company']),
            $this->field('internal_notes', 'Notes internes', group: 'interview'),
        ];
    }
}
