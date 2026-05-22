<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class ApplicationExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'applications';
    }

    public function label(): string
    {
        return 'Candidatures';
    }

    public function modelClass(): string
    {
        return Application::class;
    }

    public function defaultWith(): array
    {
        return ['user.profile', 'offer', 'program'];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where(function (Builder $q) use ($like): void {
            $q->where('application_number', 'like', $like)
                ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', $like)->orWhere('name', 'like', $like));
        });
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $statuses = (array) $filters['status'];
            $query->whereIn('status', $statuses);
        }

        if (! empty($filters['program_id'])) {
            $query->where('program_id', (int) $filters['program_id']);
        }

        if (! empty($filters['offer_id'])) {
            $query->where('offer_id', (int) $filters['offer_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            // --- Candidature
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'candidature'),
            $this->field('application_number', 'N° dossier', group: 'candidature'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'candidature'),
            $this->field('notes', 'Notes internes', group: 'candidature'),
            $this->field('created_at', 'Soumis le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'candidature'),
            $this->field('updated_at', 'Mis à jour le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'candidature'),

            // --- Candidat
            $this->field('user.id', 'Candidat ID', type: ExportFieldType::Integer, group: 'candidat', requiresWith: ['user']),
            $this->field('user.email', 'Email candidat', group: 'candidat', requiresWith: ['user']),
            $this->field('user.name', 'Nom (compte)', group: 'candidat', requiresWith: ['user']),
            $this->field('user.profile.first_name', 'Prénom', group: 'candidat', requiresWith: ['user.profile']),
            $this->field('user.profile.last_name', 'Nom', group: 'candidat', requiresWith: ['user.profile']),
            $this->field('user.phone_number1', 'Téléphone', group: 'candidat', requiresWith: ['user']),

            // --- Offre / Programme
            $this->field('offer.id', 'Offre ID', type: ExportFieldType::Integer, group: 'offre', requiresWith: ['offer']),
            $this->field('offer.title', 'Titre de l\'offre', type: ExportFieldType::Translatable, group: 'offre', requiresWith: ['offer']),
            $this->field('offer.status', 'Statut offre', type: ExportFieldType::Enum, group: 'offre', requiresWith: ['offer']),
            $this->field('program.id', 'Programme ID', type: ExportFieldType::Integer, group: 'programme', requiresWith: ['program']),
            $this->field('program.name', 'Programme', type: ExportFieldType::Translatable, group: 'programme', requiresWith: ['program']),
            $this->field('program.status', 'Statut programme', type: ExportFieldType::Enum, group: 'programme', requiresWith: ['program']),
        ];
    }
}
