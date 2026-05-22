<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum TrainingDeliveryMode: string
{
    case Online = 'ONLINE';
    case Onsite = 'ONSITE';
    case Hybrid = 'HYBRID';
}
