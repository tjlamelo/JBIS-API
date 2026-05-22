<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Certification;

use App\Core\Domain\Identity\Models\Certification;

final class DeleteCertificationAction
{
    public function execute(Certification $certification): void
    {
        $certification->delete();
    }
}
