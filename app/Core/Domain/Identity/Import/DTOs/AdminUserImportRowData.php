<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import\DTOs;

final class AdminUserImportRowData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public readonly int $line,
        public readonly string $email,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $name,
        public readonly ?string $phoneNumber1,
        public readonly ?string $phoneNumber2,
        public readonly ?string $phoneNumber3,
        public readonly ?string $gender,
        public readonly ?string $civility,
        public readonly ?string $dateOfBirth,
        public readonly ?string $placeOfBirth,
        public readonly ?string $nationalityCountryCode,
        public readonly ?string $residenceCity,
        public readonly ?string $careerIntent,
        public readonly ?string $profileType,
        public readonly ?string $maritalStatus,
        public readonly ?int $numberOfChildren,
        public readonly array $roles,
        public readonly ?bool $active,
        public readonly ?string $password,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toWriteArray(?int $nationalityCountryId = null): array
    {
        $name = $this->name;
        if (($name === null || trim($name) === '') && ($this->firstName !== null || $this->lastName !== null)) {
            $name = trim(implode(' ', array_filter([$this->firstName, $this->lastName])));
            $name = $name !== '' ? $name : null;
        }

        return array_filter([
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'name' => $name,
            'phone_number1' => $this->phoneNumber1,
            'phone_number2' => $this->phoneNumber2,
            'phone_number3' => $this->phoneNumber3,
            'gender' => $this->gender,
            'civility' => $this->civility,
            'date_of_birth' => $this->dateOfBirth,
            'place_of_birth' => $this->placeOfBirth,
            'nationality_country_id' => $nationalityCountryId,
            'residence_city' => $this->residenceCity,
            'career_intent' => $this->careerIntent,
            'profile_type' => $this->profileType,
            'marital_status' => $this->maritalStatus,
            'number_of_children' => $this->numberOfChildren,
            'roles' => $this->roles !== [] ? $this->roles : null,
            'active' => $this->active,
            'password' => $this->password,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
