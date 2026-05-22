<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Certification;

use App\Core\Domain\Identity\Models\Certification;

final class UpdateCertificationAction
{
    public function execute(Certification $certification, array $attributes): Certification
    {
        $certification->fill($attributes);
        $certification->save();

        return $certification->fresh(['document']);
    }
}
