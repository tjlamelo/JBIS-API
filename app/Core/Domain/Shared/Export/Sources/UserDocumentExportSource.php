<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;

final class UserDocumentExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_documents';
    }

    public function label(): string
    {
        return 'Documents utilisateurs';
    }

    public function modelClass(): string
    {
        return UserDocument::class;
    }

    public function defaultWith(): array
    {
        return ['user.profile', 'documentType'];
    }

    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['type'])) {
            $codes = array_map(static fn ($code): string => strtoupper((string) $code), (array) $filters['type']);
            $query->whereHas('documentType', fn ($q) => $q->whereIn('code', $codes));
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }

        if (! empty($filters['expiring_before'])) {
            $query->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', (string) $filters['expiring_before']);
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'document'),
            $this->field('documentType.code', 'Type', group: 'document', requiresWith: ['documentType']),
            $this->field('documentType.label', 'Libellé type', group: 'document', requiresWith: ['documentType']),
            $this->field('document_number', 'Numéro', group: 'document'),
            $this->field('notes', 'Notes', group: 'document'),
            $this->field('original_filename', 'Fichier', group: 'document'),
            $this->field('url', 'URL publique', group: 'document'),
            $this->field('issue_date', "Date d'émission", type: ExportFieldType::Date, format: 'd/m/Y', group: 'document'),
            $this->field('expiry_date', "Date d'expiration", type: ExportFieldType::Date, format: 'd/m/Y', group: 'document'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'document'),
            $this->field('rejection_reason', 'Motif de rejet', group: 'document'),
            $this->field('validated_at', 'Validé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'document'),

            // --- Propriétaire
            $this->field('user.email', 'Email utilisateur', group: 'utilisateur', requiresWith: ['user']),
            $this->field('user.name', 'Nom (compte)', group: 'utilisateur', requiresWith: ['user']),
            $this->field('user.profile.first_name', 'Prénom', group: 'utilisateur', requiresWith: ['user.profile']),
            $this->field('user.profile.last_name', 'Nom', group: 'utilisateur', requiresWith: ['user.profile']),
        ];
    }
}
