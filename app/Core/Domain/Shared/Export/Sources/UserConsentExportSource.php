<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserConsent;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserConsentExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_consents';
    }

    public function label(): string
    {
        return 'Consentements RGPD';
    }

    public function modelClass(): string
    {
        return UserConsent::class;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'consent'),
            $this->field('type', 'Type', type: ExportFieldType::Enum, group: 'consent'),
            $this->field('version', 'Version', group: 'consent'),
            $this->field('accepted_at', 'Accepté le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'consent'),
            $this->field('ip_address', 'Adresse IP', group: 'consent'),
        ];
    }
}
