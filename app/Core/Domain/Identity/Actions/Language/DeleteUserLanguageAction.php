<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Language;

use App\Core\Domain\Identity\Models\Language;

final class DeleteUserLanguageAction
{
    public function execute(Language $language): void
    {
        $language->delete();
    }
}
