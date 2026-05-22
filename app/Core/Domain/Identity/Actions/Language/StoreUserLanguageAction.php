<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Language;

use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Identity\Models\User;

final class StoreUserLanguageAction
{
    public function execute(User $user, array $attributes): Language
    {
        return Language::query()->create([
            ...$attributes,
            'user_id' => $user->id,
        ]);
    }
}
