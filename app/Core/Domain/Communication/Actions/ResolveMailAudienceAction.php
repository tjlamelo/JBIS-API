<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\DTOs\MailAudienceDto;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Collection;

class ResolveMailAudienceAction
{
    /**
     * @return Collection<int, User>
     */
    public function execute(MailAudienceDto $audience): Collection
    {
        $query = User::query()
            ->withValidEmail();

        if ($audience->mode === 'users') {
            $query->forUserIds($audience->userIds);
        } else {
            $query
                ->forRoles($audience->roles)
                ->forAgencies($audience->agencyIds);
        }

        return $query
            ->uniqueByEmail()
            ->get();
    }
}
