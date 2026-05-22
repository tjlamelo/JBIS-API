<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Experience;

use App\Core\Domain\Identity\Models\Experience;
use Illuminate\Support\Facades\Date;

final class ValidateExperienceAction
{
    public function execute(
        Experience $experience,
        string $status,
        int $validatorId,
    ): Experience {
        $experience->status = $status;
        $experience->approved_by = $validatorId;
        $experience->approved_at = Date::now();
        $experience->save();

        return $experience->fresh(['contractType', 'country', 'document', 'user']);
    }
}
