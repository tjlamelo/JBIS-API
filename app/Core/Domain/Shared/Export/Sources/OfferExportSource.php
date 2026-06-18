<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class OfferExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'offers';
    }

    public function label(): string
    {
        return 'Offres';
    }

    public function modelClass(): string
    {
        return Offer::class;
    }

    public function defaultWith(): array
    {
        return ['company', 'program', 'category', 'contractType', 'offerType', 'city', 'country', 'trade'];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where(function (Builder $q) use ($like): void {
            $q->whereHas('trade', function (Builder $trade) use ($like): void {
                $trade->where('name->fr', 'like', $like)
                    ->orWhere('name->en', 'like', $like);
            })->orWhere('description->fr', 'like', $like)
                ->orWhere('description->en', 'like', $like);
        });
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }

        if (! empty($filters['program_id'])) {
            $query->where('program_id', (int) $filters['program_id']);
        }

        if (! empty($filters['company_id'])) {
            $query->where('company_id', (int) $filters['company_id']);
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'offre'),
            $this->field('trade.name', 'Métier', type: ExportFieldType::Translatable, group: 'offre', requiresWith: ['trade']),
            $this->field('slug', 'Slug', type: ExportFieldType::Translatable, group: 'offre'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'offre'),
            $this->field('work_mode', 'Mode de travail', group: 'offre'),
            $this->field('address', 'Adresse', group: 'offre'),
            $this->field('available_positions', 'Postes disponibles', type: ExportFieldType::Integer, group: 'offre'),
            $this->field('salary_min', 'Salaire min', type: ExportFieldType::Float, group: 'offre'),
            $this->field('salary_max', 'Salaire max', type: ExportFieldType::Float, group: 'offre'),
            $this->field('currency', 'Devise', group: 'offre'),
            $this->field('published_at', 'Publié le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'offre'),
            $this->field('expiration_date', 'Expire le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'offre'),
            $this->field('created_at', 'Créé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'offre'),

            $this->field('company.name', 'Entreprise', group: 'entreprise', requiresWith: ['company']),
            $this->field('program.name', 'Programme', type: ExportFieldType::Translatable, group: 'programme', requiresWith: ['program']),
            $this->field('category.name', 'Catégorie', type: ExportFieldType::Translatable, group: 'classification', requiresWith: ['category']),
            $this->field('contractType.name', 'Type de contrat', type: ExportFieldType::Translatable, group: 'classification', requiresWith: ['contractType']),
            $this->field('offerType.name', "Type d'offre", type: ExportFieldType::Translatable, group: 'classification', requiresWith: ['offerType']),
            $this->field('city.name', 'Ville', type: ExportFieldType::Translatable, group: 'localisation', requiresWith: ['city']),
            $this->field('country.name', 'Pays', type: ExportFieldType::Translatable, group: 'localisation', requiresWith: ['country']),
        ];
    }
}
