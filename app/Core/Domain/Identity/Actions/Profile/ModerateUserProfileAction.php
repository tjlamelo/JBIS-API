<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;

final class ModerateUserProfileAction
{
    public function execute(User $user, bool $isApproved, int $moderatorId): UserProfile
    {
        $profile = $user->profile()->firstOrNew(['user_id' => $user->id]);

        if (! $profile->exists && ! $isApproved) {
            throw new \InvalidArgumentException(__('Aucun profil candidat à modérer.'));
        }

        $profile->is_approved = $isApproved;
        $profile->approved_by = $isApproved ? $moderatorId : null;
        $profile->save();
        $profile->loadMissing('approver:id,name');

        return $profile;
    }
}
