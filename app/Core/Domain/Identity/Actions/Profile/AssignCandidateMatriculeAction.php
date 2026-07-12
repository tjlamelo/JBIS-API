<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Enums\MatriculeService;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\MatriculeGenerator;

/**
 * Attribution automatique (ex. à la création d'une candidature).
 * N'écrase jamais un matricule existant.
 */
final class AssignCandidateMatriculeAction
{
    public function __construct(
        private readonly MatriculeGenerator $generator,
    ) {}

    public function execute(User $user, ?string $matricule = null): string
    {
        $profile = $user->profile ?? UserProfile::query()->firstOrCreate(['user_id' => $user->id]);

        if (is_string($profile->matricule) && trim($profile->matricule) !== '') {
            return trim($profile->matricule);
        }

        if ($matricule !== null && trim($matricule) !== '') {
            $value = trim($matricule);
            $profile->update(['matricule' => $value]);

            return $value;
        }

        $service = $this->resolveService($user);
        $value = $this->generator->generate($service->value);
        $profile->update(['matricule' => $value]);

        return $value;
    }

    private function resolveService(User $user): MatriculeService
    {
        $user->loadMissing('profile');
        $intent = (string) ($user->profile?->career_intent ?? '');

        return match ($intent) {
            'work_abroad' => MatriculeService::PlacementInternational,
            'work_local' => MatriculeService::PlacementNational,
            'visa_support' => MatriculeService::VisaEtudiant,
            default => MatriculeService::Candidat,
        };
    }
}
