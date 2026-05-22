<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class UserExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'users';
    }

    public function label(): string
    {
        return 'Utilisateurs';
    }

    public function modelClass(): string
    {
        return User::class;
    }

    public function defaultWith(): array
    {
        return ['profile'];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where(function (Builder $q) use ($like): void {
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone_number1', 'like', $like);
        });
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['role']) && method_exists($query->getModel(), 'hasRole')) {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', (string) $filters['role']));
        }

        if (! empty($filters['email_verified'])) {
            $query->whereNotNull('email_verified_at');
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            // --- Compte
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'compte'),
            $this->field('name', 'Nom complet (compte)', group: 'compte'),
            $this->field('email', 'Email', group: 'compte'),
            $this->field('phone_number1', 'Téléphone', group: 'compte'),
            $this->field('active', 'Actif', type: ExportFieldType::Boolean, group: 'compte'),
            $this->field('email_verified_at', 'Email vérifié le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'compte'),
            $this->field('created_at', 'Créé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'compte'),

            // --- Profil
            $this->field('profile.first_name', 'Prénom', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.last_name', 'Nom', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.gender', 'Genre', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.date_of_birth', 'Date de naissance', type: ExportFieldType::Date, format: 'd/m/Y', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.place_of_birth', 'Lieu de naissance', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.address', 'Adresse', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.marital_status', 'Situation matrimoniale', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.number_of_children', 'Nb enfants', type: ExportFieldType::Integer, group: 'profil', requiresWith: ['profile']),
            $this->field('profile.matricule', 'Matricule', group: 'profil', requiresWith: ['profile']),
            $this->field('profile.is_approved', 'Profil approuvé', type: ExportFieldType::Boolean, group: 'profil', requiresWith: ['profile']),

            // --- Statistiques (relations)
            $this->field(
                'documents_count',
                'Nb documents soumis',
                path: 'documents',
                type: ExportFieldType::Count,
                default: 0,
                group: 'statistiques',
                requiresWith: ['documents'],
            ),
            $this->field(
                'applications_count',
                'Nb candidatures',
                path: 'applications',
                type: ExportFieldType::Count,
                default: 0,
                group: 'statistiques',
                requiresWith: ['applications'],
            ),
            $this->field(
                'experiences_count',
                'Nb expériences',
                path: 'experiences',
                type: ExportFieldType::Count,
                default: 0,
                group: 'statistiques',
                requiresWith: ['experiences'],
            ),
            $this->field(
                'educations_count',
                'Nb formations',
                path: 'educations',
                type: ExportFieldType::Count,
                default: 0,
                group: 'statistiques',
                requiresWith: ['educations'],
            ),

            // --- Listes (champs agrégés simples)
            $this->field(
                'documents_types',
                'Types de documents',
                path: 'documents.*.type',
                type: ExportFieldType::Array,
                format: ', ',
                group: 'documents',
                requiresWith: ['documents'],
            ),
            $this->field(
                'applications_statuses',
                'Statuts de candidatures',
                path: 'applications.*.status',
                type: ExportFieldType::Array,
                format: ', ',
                group: 'candidatures',
                requiresWith: ['applications'],
            ),
        ];
    }
}
