<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class InterestExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'interests';
    }

    public function label(): string
    {
        return 'Centres d\'intérêt';
    }

    public function modelClass(): string
    {
        return InterestAndHobby::class;
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'interest'),
            $this->field('title', 'Libellé', group: 'interest'),
        ];
    }
}
