<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Agency;

use App\Core\Domain\Catalog\DTOs\Agency\AgencyDto;
use App\Core\Domain\Catalog\Mappers\Agency\AgencyAttributeMapper;
use App\Core\Domain\Catalog\Models\Agency;

final class CreateAgencyAction
{
    public function __construct(
        private readonly AgencyAttributeMapper $attributeMapper,
    ) {}

    public function execute(AgencyDto $dto): Agency
    {
        $agency = new Agency;
        $this->attributeMapper->apply($agency, $dto, isCreate: true);
        $agency->save();

        return $agency->refresh();
    }
}
