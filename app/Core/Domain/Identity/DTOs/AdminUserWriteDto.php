<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Recruiter\Enums\ProfileOrigin;

readonly class AdminUserWriteDto
{
    /**
     * @param  list<string>|null  $roles
     */
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phoneNumber1 = null,
        public ?string $phoneNumber2 = null,
        public ?string $phoneNumber3 = null,
        public ?string $password = null,
        public ?bool $active = null,
        public ?array $roles = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $civility = null,
        public ?string $dateOfBirth = null,
        public ?string $placeOfBirth = null,
        public ?int $nationalityCountryId = null,
        public ?string $residenceCity = null,
        public ?string $careerIntent = null,
        public ?string $profileType = null,
        public ?int $highestEducationLevelId = null,
        public ?string $gender = null,
        public ?string $maritalStatus = null,
        public ?int $numberOfChildren = null,
        public bool $emailIsPlaceholder = false,
        public bool $sendAccountEmail = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $gender = isset($data['gender']) && is_string($data['gender']) && $data['gender'] !== ''
            ? (string) $data['gender']
            : null;
        $civilityRaw = isset($data['civility']) && is_string($data['civility']) && $data['civility'] !== ''
            ? (string) $data['civility']
            : null;

        return new self(
            name: isset($data['name']) && $data['name'] !== '' ? (string) $data['name'] : null,
            email: isset($data['email']) && is_string($data['email']) && trim($data['email']) !== ''
                ? trim($data['email'])
                : null,
            phoneNumber1: array_key_exists('phone_number1', $data)
                ? ($data['phone_number1'] !== null && $data['phone_number1'] !== '' ? (string) $data['phone_number1'] : null)
                : null,
            phoneNumber2: array_key_exists('phone_number2', $data)
                ? ($data['phone_number2'] !== null && $data['phone_number2'] !== '' ? (string) $data['phone_number2'] : null)
                : null,
            phoneNumber3: array_key_exists('phone_number3', $data)
                ? ($data['phone_number3'] !== null && $data['phone_number3'] !== '' ? (string) $data['phone_number3'] : null)
                : null,
            password: isset($data['password']) && $data['password'] !== '' ? (string) $data['password'] : null,
            active: array_key_exists('active', $data) ? (bool) $data['active'] : null,
            roles: isset($data['roles']) && is_array($data['roles'])
                ? array_values(array_map('strval', $data['roles']))
                : null,
            firstName: isset($data['first_name']) && $data['first_name'] !== '' ? (string) $data['first_name'] : null,
            lastName: isset($data['last_name']) && $data['last_name'] !== '' ? (string) $data['last_name'] : null,
            civility: Civility::normalize($civilityRaw, $gender),
            dateOfBirth: isset($data['date_of_birth']) && $data['date_of_birth'] !== ''
                ? (string) $data['date_of_birth']
                : null,
            placeOfBirth: isset($data['place_of_birth']) && $data['place_of_birth'] !== ''
                ? (string) $data['place_of_birth']
                : null,
            nationalityCountryId: isset($data['nationality_country_id']) && $data['nationality_country_id'] !== ''
                ? (int) $data['nationality_country_id']
                : null,
            residenceCity: isset($data['residence_city']) && $data['residence_city'] !== ''
                ? (string) $data['residence_city']
                : null,
            careerIntent: isset($data['career_intent']) && $data['career_intent'] !== ''
                ? (string) $data['career_intent']
                : null,
            profileType: isset($data['profile_type']) && $data['profile_type'] !== ''
                ? (string) $data['profile_type']
                : null,
            highestEducationLevelId: isset($data['highest_education_level_id']) && $data['highest_education_level_id'] !== ''
                ? (int) $data['highest_education_level_id']
                : null,
            gender: $gender,
            maritalStatus: isset($data['marital_status']) && $data['marital_status'] !== ''
                ? (string) $data['marital_status']
                : null,
            numberOfChildren: array_key_exists('number_of_children', $data) && $data['number_of_children'] !== null && $data['number_of_children'] !== ''
                ? (int) $data['number_of_children']
                : null,
            emailIsPlaceholder: (bool) ($data['email_is_placeholder'] ?? false),
            sendAccountEmail: (bool) ($data['send_account_email'] ?? false),
        );
    }

    public function resolvedName(): ?string
    {
        if ($this->name !== null && trim($this->name) !== '') {
            return trim($this->name);
        }

        $parts = array_filter([$this->firstName, $this->lastName], static fn (?string $v): bool => $v !== null && trim($v) !== '');

        return $parts !== [] ? implode(' ', $parts) : null;
    }

    public function resolvedPassword(): string
    {
        if ($this->password !== null && $this->password !== '') {
            return $this->password;
        }

        return (string) config('identity.default_user_password');
    }

    public function hasProfileData(): bool
    {
        return $this->firstName !== null
            || $this->lastName !== null
            || $this->civility !== null
            || $this->dateOfBirth !== null
            || $this->placeOfBirth !== null
            || $this->nationalityCountryId !== null
            || $this->residenceCity !== null
            || $this->careerIntent !== null
            || $this->profileType !== null
            || $this->highestEducationLevelId !== null
            || $this->gender !== null
            || $this->maritalStatus !== null
            || $this->numberOfChildren !== null
            || $this->phoneNumber2 !== null
            || $this->phoneNumber3 !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toUserAttributes(): array
    {
        $attributes = [];

        $name = $this->resolvedName();
        if ($name !== null) {
            $attributes['name'] = $name;
        }
        if ($this->email !== null) {
            $attributes['email'] = $this->email;
        }
        $attributes['email_is_placeholder'] = $this->emailIsPlaceholder;
        if ($this->phoneNumber1 !== null) {
            $attributes['phone_number1'] = $this->phoneNumber1;
        }

        $attributes['password'] = $this->resolvedPassword();

        if (! $this->emailIsPlaceholder && $this->sendAccountEmail) {
            $attributes['must_change_password'] = true;
            $attributes['email_verified_at'] = now();
        }

        if ($this->active !== null) {
            $attributes['active'] = $this->active;
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function toProfileAttributes(): array
    {
        $attributes = [
            'profile_origin' => ProfileOrigin::Staff->value,
        ];

        if ($this->firstName !== null) {
            $attributes['first_name'] = $this->firstName;
        }
        if ($this->lastName !== null) {
            $attributes['last_name'] = $this->lastName;
        }
        if ($this->civility !== null) {
            $attributes['civility'] = $this->civility;
        }
        if ($this->dateOfBirth !== null) {
            $attributes['date_of_birth'] = $this->dateOfBirth;
        }
        if ($this->placeOfBirth !== null) {
            $attributes['place_of_birth'] = $this->placeOfBirth;
        }
        if ($this->nationalityCountryId !== null) {
            $attributes['nationality_country_id'] = $this->nationalityCountryId;
        }
        if ($this->residenceCity !== null) {
            $attributes['residence_city'] = $this->residenceCity;
        }
        if ($this->careerIntent !== null) {
            $attributes['career_intent'] = $this->careerIntent;
        }
        if ($this->profileType !== null) {
            $attributes['profile_type'] = $this->profileType;
        }
        if ($this->highestEducationLevelId !== null) {
            $attributes['highest_education_level_id'] = $this->highestEducationLevelId;
        }
        if ($this->gender !== null) {
            $attributes['gender'] = $this->gender;
        }
        if ($this->maritalStatus !== null) {
            $attributes['marital_status'] = $this->maritalStatus;
        }
        if ($this->numberOfChildren !== null) {
            $attributes['number_of_children'] = $this->numberOfChildren;
        }
        if ($this->phoneNumber2 !== null) {
            $attributes['phone_number2'] = $this->phoneNumber2;
        }
        if ($this->phoneNumber3 !== null) {
            $attributes['phone_number3'] = $this->phoneNumber3;
        }

        return $attributes;
    }
}
