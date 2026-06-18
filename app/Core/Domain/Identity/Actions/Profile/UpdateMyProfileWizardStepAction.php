<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Actions\User\SyncUserTradesAction;
use App\Core\Domain\Identity\Exceptions\ProfileLockedException;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Identity\Support\ProfilePicturesSerializer;
use Illuminate\Support\Arr;

final class UpdateMyProfileWizardStepAction
{
    public function __construct(
        private readonly ProfilePicturesSerializer $picturesSerializer,
        private readonly SyncUserTradesAction $syncUserTrades,
    ) {}

    private const STAFF_ROLES = ApplicationRole::STAFF_ROLES;

    /** @var array<string, list<string>> */
    private const STEP_FIELDS = [
        'personal' => [
            'first_name',
            'last_name',
            'date_of_birth',
            'place_of_birth',
            'nationality_country_id',
            'residence_city',
            'career_intent',
            'highest_education_level_id',
            'gender',
            'marital_status',
            'number_of_children',
        ],
        'contact' => [
            'address',
            'phone_number2',
            'phone_number3',
            'email_institutional',
        ],
        'professional' => [
            'matricule',
            'agency_id',
            'bio',
        ],
        'documents' => [
            'pictures',
        ],
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(User $user, string $step, array $payload): UserProfile
    {
        if (! isset(self::STEP_FIELDS[$step])) {
            throw new \InvalidArgumentException(__('Étape de profil invalide.'));
        }

        $profile = $user->profile()->firstOrNew(['user_id' => $user->id]);

        if ($profile->exists && $profile->is_approved && ! $user->hasAnyRole(self::STAFF_ROLES)) {
            throw new ProfileLockedException;
        }

        $allowedFields = self::STEP_FIELDS[$step];

        if (! $user->hasAnyRole(self::STAFF_ROLES)) {
            if ($step === 'contact') {
                $allowedFields = array_values(array_diff($allowedFields, ['email_institutional']));
            }
            if ($step === 'professional') {
                $allowedFields = array_values(array_diff($allowedFields, ['matricule']));
            }
        }

        $attributes = Arr::only($payload, $allowedFields);

        if ($step === 'documents' && isset($attributes['pictures']) && is_array($attributes['pictures'])) {
            $attributes['pictures'] = $this->picturesSerializer->normalizeForStorage($attributes['pictures']);
        }

        $profile->fill($attributes);

        if ($step === 'personal' && isset($payload['trades']) && is_array($payload['trades'])) {
            $this->syncUserTrades->execute($user, $payload['trades']);
        }

        if ($user->hasAnyRole(self::STAFF_ROLES)) {
            $profile->is_approved = true;
            $profile->approved_by = $user->id;
        }

        $profile->save();
        $profile->loadMissing(['approver:id,name', 'highestEducationLevel:id,name,slug']);

        return $profile;
    }
}
