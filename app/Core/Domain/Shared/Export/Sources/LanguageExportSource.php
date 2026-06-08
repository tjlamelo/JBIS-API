<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class LanguageExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'languages';
    }

    public function label(): string
    {
        return 'Langues';
    }

    public function modelClass(): string
    {
        return Language::class;
    }

    public function defaultWith(): array
    {
        return ['language', 'languageLevel'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'language'),
            $this->field('language.name', 'Langue', type: ExportFieldType::Translatable, group: 'language', requiresWith: ['language']),
            $this->field('languageLevel.name', 'Niveau', type: ExportFieldType::Translatable, group: 'language', requiresWith: ['languageLevel']),
            $this->field('is_approved', 'Approuvé', type: ExportFieldType::Boolean, group: 'language'),
            $this->field('reviewed_at', 'Révisé le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'language'),
        ];
    }
}
