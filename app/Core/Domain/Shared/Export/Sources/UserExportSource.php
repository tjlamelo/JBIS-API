<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\AdminUserSearchFilterApplicator;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class UserExportSource extends AbstractEloquentExportSource
{
    public function __construct(
        private readonly AdminUserSearchFilterApplicator $searchFilters,
    ) {}

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

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        return $this->searchFilters->apply($query, $filters);
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
            $this->field(
                'profile.total_years_of_experience',
                'Années d\'expérience',
                type: ExportFieldType::Integer,
                group: 'profil',
                requiresWith: ['profile'],
            ),

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
