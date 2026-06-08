<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserNote;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserNoteExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_notes';
    }

    public function label(): string
    {
        return 'Notes internes';
    }

    public function modelClass(): string
    {
        return UserNote::class;
    }

    public function defaultWith(): array
    {
        return ['author'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'note'),
            $this->field('content', 'Contenu', group: 'note'),
            $this->field('is_private', 'Privée', type: ExportFieldType::Boolean, group: 'note'),
            $this->field('author.name', 'Auteur', group: 'note', requiresWith: ['author']),
            $this->field('created_at', 'Créée le', type: ExportFieldType::DateTime, format: 'd/m/Y H:i', group: 'note'),
        ];
    }
}
