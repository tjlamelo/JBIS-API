<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Certification;

use App\Core\Domain\Identity\Models\Certification;
use Illuminate\Support\Facades\Date;

final class ValidateCertificationAction
{
    public function execute(
        Certification $certification,
        string $status,
        int $validatorId,
    ): Certification {
        $certification->status = $status;
        $certification->approved_by = $validatorId;
        $certification->approved_at = Date::now();
        $certification->save();

        return $certification->fresh(['document', 'user']);
    }
}
