<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class CertificationExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'certifications';
    }

    public function label(): string
    {
        return 'Certifications';
    }

    public function modelClass(): string
    {
        return Certification::class;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'certification'),
            $this->field('name', 'Nom', group: 'certification'),
            $this->field('issuing_organization', 'Organisme', group: 'certification'),
            $this->field('credential_id', 'Identifiant', group: 'certification'),
            $this->field('credential_url', 'URL', group: 'certification'),
            $this->field('issue_date', "Date d'émission", type: ExportFieldType::Date, format: 'd/m/Y', group: 'certification'),
            $this->field('expiry_date', "Date d'expiration", type: ExportFieldType::Date, format: 'd/m/Y', group: 'certification'),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'certification'),
            $this->field('approved_at', 'Approuvé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'certification'),
        ];
    }
}
