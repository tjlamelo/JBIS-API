<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;

final class AssignCandidateMatriculeAction
{
    public function execute(User $user, ?string $matricule = null): string
    {
        $profile = $user->profile ?? UserProfile::query()->firstOrCreate(['user_id' => $user->id]);

        if (is_string($profile->matricule) && trim($profile->matricule) !== '') {
            return trim($profile->matricule);
        }

        $matricule = trim((string) ($matricule ?? $this->generate($user)));
        $profile->update(['matricule' => $matricule]);

        return $matricule;
    }

    private function generate(User $user): string
    {
        return sprintf('JBIS-C-%d-%05d', (int) date('Y'), (int) $user->id);
    }
}
