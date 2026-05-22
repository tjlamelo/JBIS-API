<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Language;

use App\Core\Domain\Identity\Models\Language;
use Illuminate\Support\Facades\Date;

final class ApproveUserLanguageAction
{
    public function execute(Language $language, int $approverId, bool $isApproved): Language
    {
        $language->is_approved = $isApproved;
        $language->approved_by = $isApproved ? $approverId : null;
        $language->reviewed_at = Date::now();
        $language->save();

        return $language->fresh(['user']);
    }
}
