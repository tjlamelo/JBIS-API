<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserVisaHistory;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserVisaHistoryExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_visa_histories';
    }

    public function label(): string
    {
        return 'Historique visas';
    }

    public function modelClass(): string
    {
        return UserVisaHistory::class;
    }

    public function defaultWith(): array
    {
        return ['country'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'visa'),
            $this->field('country.name', 'Pays', type: ExportFieldType::Translatable, group: 'visa', requiresWith: ['country']),
            $this->field('visa_type', 'Type de visa', group: 'visa'),
            $this->field('visa_number', 'Numéro', group: 'visa'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'visa'),
            $this->field('issue_date', "Date d'émission", type: ExportFieldType::Date, format: 'd/m/Y', group: 'visa'),
            $this->field('expiry_date', "Date d'expiration", type: ExportFieldType::Date, format: 'd/m/Y', group: 'visa'),
            $this->field('rejection_reason', 'Motif de rejet', group: 'visa'),
            $this->field('rejection_date', 'Date de rejet', type: ExportFieldType::Date, format: 'd/m/Y', group: 'visa'),
        ];
    }
}
