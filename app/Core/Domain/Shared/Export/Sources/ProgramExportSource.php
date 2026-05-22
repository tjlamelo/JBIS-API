<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class ProgramExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'programs';
    }

    public function label(): string
    {
        return 'Programmes';
    }

    public function modelClass(): string
    {
        return Program::class;
    }

    public function defaultWith(): array
    {
        return ['geographicZone'];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where(function (Builder $q) use ($like): void {
            $q->where('name->fr', 'like', $like)
                ->orWhere('name->en', 'like', $like);
        });
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }

        if (! empty($filters['geographic_zone_id'])) {
            $query->where('geographic_zone_id', (int) $filters['geographic_zone_id']);
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'programme'),
            $this->field('name', 'Nom', type: ExportFieldType::Translatable, group: 'programme'),
            $this->field('slug', 'Slug', type: ExportFieldType::Translatable, group: 'programme'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'programme'),
            $this->field('procedure_duration', 'Durée procédure', type: ExportFieldType::Integer, group: 'programme'),
            $this->field('duration_unit', 'Unité de durée', group: 'programme'),
            $this->field('age_min', 'Âge min', type: ExportFieldType::Integer, group: 'programme'),
            $this->field('age_max', 'Âge max', type: ExportFieldType::Integer, group: 'programme'),
            $this->field('is_featured', 'Mis en avant', type: ExportFieldType::Boolean, group: 'programme'),
            $this->field('is_urgent', 'Urgent', type: ExportFieldType::Boolean, group: 'programme'),
            $this->field('views_count', 'Vues', type: ExportFieldType::Integer, group: 'programme'),
            $this->field('start_date', 'Début', type: ExportFieldType::Date, format: 'd/m/Y', group: 'programme'),
            $this->field('end_date', 'Fin', type: ExportFieldType::Date, format: 'd/m/Y', group: 'programme'),
            $this->field('published_at', 'Publié le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'programme'),
            $this->field('created_at', 'Créé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'programme'),

            $this->field('geographicZone.name', 'Zone géographique', type: ExportFieldType::Translatable, group: 'localisation', requiresWith: ['geographicZone']),

            $this->field(
                'offers_count',
                'Nb offres',
                path: 'offers',
                type: ExportFieldType::Count,
                default: 0,
                group: 'statistiques',
                requiresWith: ['offers'],
            ),
        ];
    }
}
