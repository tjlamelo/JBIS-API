<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Profile;

use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ProfilePicturesSerializer;
use Illuminate\Support\Arr;

final class UpdateAdminUserProfileWizardStepAction
{
    public function __construct(
        private readonly ProfilePicturesSerializer $picturesSerializer,
    ) {}

    /** @var array<string, list<string>> */
    private const STEP_FIELDS = [
        'personal' => [
            'first_name',
            'last_name',
            'civility',
            'date_of_birth',
            'place_of_birth',
            'nationality_country_id',
            'residence_city',
            'career_intent',
            'profile_type',
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
        $attributes = Arr::only($payload, self::STEP_FIELDS[$step]);

        if ($step === 'personal') {
            $gender = isset($attributes['gender'])
                ? (string) $attributes['gender']
                : ($profile->gender ?: null);
            $civility = array_key_exists('civility', $attributes)
                ? (is_string($attributes['civility']) ? $attributes['civility'] : null)
                : $profile->civility;
            $attributes['civility'] = Civility::normalize($civility, $gender);
        }

        if ($step === 'documents' && isset($attributes['pictures']) && is_array($attributes['pictures'])) {
            $attributes['pictures'] = $this->picturesSerializer->normalizeForStorage($attributes['pictures']);
        }

        $profile->fill($attributes);
        $profile->save();
        $profile->loadMissing('approver:id,name');

        return $profile;
    }
}
