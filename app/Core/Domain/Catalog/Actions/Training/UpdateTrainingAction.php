<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Training;

use App\Core\Domain\Catalog\DTOs\Training\TrainingDto;
use App\Core\Domain\Catalog\Mappers\Training\TrainingAttributeMapper;
use App\Core\Domain\Catalog\Models\Training;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateTrainingAction
{
    public function __construct(
        private readonly TrainingAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $trainingId, TrainingDto $dto): Training
    {
        /** @var Training|null $training */
        $training = Training::query()->find($trainingId);

        if (! $training) {
            throw new ModelNotFoundException("Training {$trainingId} not found.");
        }

        $this->attributeMapper->apply($training, $dto, isCreate: false);
        $training->save();

        return $training->refresh();
    }
}
