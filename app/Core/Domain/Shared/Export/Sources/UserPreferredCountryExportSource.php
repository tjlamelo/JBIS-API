<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserPreferredCountry;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserPreferredCountryExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_preferred_countries';
    }

    public function label(): string
    {
        return 'Pays cibles';
    }

    public function modelClass(): string
    {
        return UserPreferredCountry::class;
    }

    public function defaultWith(): array
    {
        return ['country'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'mobility'),
            $this->field('country.name', 'Pays', type: ExportFieldType::Translatable, group: 'mobility', requiresWith: ['country']),
            $this->field('country.code', 'Code pays', group: 'mobility', requiresWith: ['country']),
            $this->field('priority', 'Priorité', type: ExportFieldType::Integer, group: 'mobility'),
        ];
    }
}
