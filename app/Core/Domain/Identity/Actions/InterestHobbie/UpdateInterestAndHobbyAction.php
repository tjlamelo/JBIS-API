<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\InterestHobbie;

use App\Core\Domain\Identity\Models\InterestAndHobby;

final class UpdateInterestAndHobbyAction
{
    public function execute(InterestAndHobby $interestAndHobby, array $attributes): InterestAndHobby
    {
        $interestAndHobby->fill($attributes);
        $interestAndHobby->save();

        return $interestAndHobby->fresh();
    }
}
