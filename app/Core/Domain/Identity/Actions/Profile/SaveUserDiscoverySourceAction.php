<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use Illuminate\Validation\ValidationException;

final class SaveUserDiscoverySourceAction
{
    /**
     * @return array{profile: UserProfile}
     */
    public function execute(User $user, int $discoverySourceId, ?string $discoverySourceOther = null): array
    {
        $source = DiscoverySource::query()
            ->where('is_active', true)
            ->find($discoverySourceId);

        if ($source === null) {
            throw ValidationException::withMessages([
                'discovery_source_id' => [__('Source de provenance invalide.')],
            ]);
        }

        $other = $discoverySourceOther;
        if ($source->key === 'other') {
            if ($other === null || trim($other) === '') {
                throw ValidationException::withMessages([
                    'discovery_source_other' => [__('Veuillez préciser comment vous avez connu JBIS.')],
                ]);
            }
        } else {
            $other = null;
        }

        $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
        $firstName = $parts[0] ?? null;
        $lastName = $parts[1] ?? null;

        $profile = UserProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
        );

        $profile->forceFill([
            'discovery_source_id' => $source->id,
            'discovery_source_other' => $other,
        ])->save();

        return ['profile' => $profile->fresh(['discoverySource:id,key,label'])];
    }
}
