<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Enums\MatriculeService;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\MatriculeGenerator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class AssignUserMatriculeAction
{
    public function __construct(
        private readonly MatriculeGenerator $generator,
    ) {}

    /**
     * @return array{
     *     user_id: int,
     *     service: string,
     *     matricule: string,
     *     previous_matricule: string|null,
     *     regenerated: bool
     * }
     */
    public function execute(
        User $user,
        string $serviceKey,
        ?string $customTag = null,
        bool $force = false,
    ): array {
        if (MatriculeService::tryFrom($serviceKey) === null) {
            throw new InvalidArgumentException(__('Service de matricule invalide.'));
        }

        $profile = $user->profile ?? UserProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
        );

        $previous = is_string($profile->matricule) && trim($profile->matricule) !== ''
            ? trim($profile->matricule)
            : null;

        if ($previous !== null && ! $force) {
            throw new InvalidArgumentException(
                __('Un matricule existe déjà (:matricule). Passez force=true pour le régénérer.', [
                    'matricule' => $previous,
                ]),
            );
        }

        $matricule = $this->generator->generate($serviceKey, $customTag);
        $profile->update(['matricule' => $matricule]);

        Log::info('matricule_assigned', [
            'user_id' => $user->id,
            'service' => $serviceKey,
            'custom_tag' => $customTag,
            'matricule' => $matricule,
            'previous_matricule' => $previous,
            'force' => $force,
        ]);

        return [
            'user_id' => $user->id,
            'service' => $serviceKey,
            'matricule' => $matricule,
            'previous_matricule' => $previous,
            'regenerated' => $previous !== null,
        ];
    }
}
