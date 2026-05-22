<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\InterestHobbie;

use App\Core\Domain\Identity\Models\InterestAndHobby;

final class DeleteInterestAndHobbyAction
{
    public function execute(InterestAndHobby $interestAndHobby): void
    {
        $interestAndHobby->delete();
    }
}
