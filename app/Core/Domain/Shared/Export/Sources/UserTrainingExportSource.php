<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Identity\Models\UserTraining;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class UserTrainingExportSource extends AbstractEloquentExportSource
{
    public function key(): string
    {
        return 'user_trainings';
    }

    public function label(): string
    {
        return 'Formations JBIS';
    }

    public function modelClass(): string
    {
        return UserTraining::class;
    }

    public function defaultWith(): array
    {
        return ['training'];
    }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer, group: 'training'),
            $this->field('training.name', 'Formation', type: ExportFieldType::Translatable, group: 'training', requiresWith: ['training']),
            $this->field('status', 'Statut', type: ExportFieldType::Enum, group: 'training'),
            $this->field('started_at', 'Début', type: ExportFieldType::Date, format: 'd/m/Y', group: 'training'),
            $this->field('finished_at', 'Fin', type: ExportFieldType::Date, format: 'd/m/Y', group: 'training'),
        ];
    }
}
