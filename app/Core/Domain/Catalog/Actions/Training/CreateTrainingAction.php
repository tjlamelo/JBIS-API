<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Training;

use App\Core\Domain\Catalog\DTOs\Training\TrainingDto;
use App\Core\Domain\Catalog\Mappers\Training\TrainingAttributeMapper;
use App\Core\Domain\Catalog\Models\Training;

final class CreateTrainingAction
{
    public function __construct(
        private readonly TrainingAttributeMapper $attributeMapper,
    ) {}

    public function execute(TrainingDto $dto): Training
    {
        $training = new Training;
        $this->attributeMapper->apply($training, $dto, isCreate: true);
        $training->save();

        return $training->refresh();
    }
}
