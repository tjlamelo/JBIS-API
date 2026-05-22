<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Agency;

use App\Core\Domain\Catalog\DTOs\Agency\AgencyDto;
use App\Core\Domain\Catalog\Mappers\Agency\AgencyAttributeMapper;
use App\Core\Domain\Catalog\Models\Agency;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateAgencyAction
{
    public function __construct(
        private readonly AgencyAttributeMapper $attributeMapper,
    ) {}

    public function execute(int $agencyId, AgencyDto $dto): Agency
    {
        /** @var Agency|null $agency */
        $agency = Agency::query()->find($agencyId);

        if (! $agency) {
            throw new ModelNotFoundException("Agency {$agencyId} not found.");
        }

        $this->attributeMapper->apply($agency, $dto, isCreate: false);
        $agency->save();

        return $agency->refresh();
    }
}
