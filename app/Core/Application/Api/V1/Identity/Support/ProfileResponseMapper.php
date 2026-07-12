<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Support;

use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ProfilePicturesSerializer;
use App\Core\Application\Api\Support\EnumOptionPresenter;
use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Identity\Enums\ProfileType;

final class ProfileResponseMapper
{
    public function __construct(
        private readonly ProfilePicturesSerializer $picturesSerializer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(UserProfile $profile): array
    {
        return [
            'is_approved' => $profile->is_approved,
            'approved_by' => $profile->approved_by,
            'approved_by_name' => $profile->approver?->name,
            'updated_at' => $profile->updated_at?->toIso8601String(),
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'civility' => $profile->civility,
            'civility_label' => EnumOptionPresenter::present($profile->civility, Civility::class),
            'date_of_birth' => $profile->date_of_birth?->format('Y-m-d'),
            'age' => $profile->age(),
            'place_of_birth' => $profile->place_of_birth,
            'nationality_country_id' => $profile->nationality_country_id,
            'residence_city' => $profile->residence_city,
            'career_intent' => $profile->career_intent,
            'career_intent_label' => EnumOptionPresenter::present($profile->career_intent, CareerIntent::class),
            'profile_type' => $profile->profile_type,
            'profile_type_label' => EnumOptionPresenter::present($profile->profile_type, ProfileType::class),
            'highest_education_level_id' => $profile->highest_education_level_id,
            'highest_education_level' => $profile->highestEducationLevel ? [
                'id' => $profile->highestEducationLevel->id,
                'name' => $profile->highestEducationLevel->getTranslations('name'),
                'slug' => $profile->highestEducationLevel->slug,
            ] : null,
            'gender' => $profile->gender,
            'marital_status' => $profile->marital_status,
            'number_of_children' => $profile->number_of_children,
            'address' => $profile->address,
            'phone_number2' => $profile->phone_number2,
            'phone_number3' => $profile->phone_number3,
            'email_institutional' => $profile->email_institutional,
            'matricule' => $profile->matricule,
            'agency_id' => $profile->agency_id,
            'bio' => $profile->bio,
            'pictures' => $this->picturesSerializer->toUrls($profile->pictures),
            'discovery_source_id' => $profile->discovery_source_id,
            'discovery_source_other' => $profile->discovery_source_other,
        ];
    }
}
