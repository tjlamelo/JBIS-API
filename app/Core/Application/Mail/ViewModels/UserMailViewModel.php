<?php

namespace App\Core\Application\Mail\ViewModels;

use App\Core\Domain\Identity\Models\User;

final readonly class UserMailViewModel
{
    /**
     * @param array<string, mixed> $custom
     */
    public function __construct(
        private ?User $user,
        private array $custom = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toContext(): array
    {
        $profile = $this->user?->profile;
        $agency = $profile?->agency;
        $firstName = (string) ($profile?->first_name ?? '');
        $lastName = (string) ($profile?->last_name ?? '');

        return [
            'user' => [
                'id' => $this->user?->id,
                'name' => (string) ($this->user?->name ?? ''),
                'email' => (string) ($this->user?->email ?? ''),
                'phone_number1' => (string) ($this->user?->phone_number1 ?? ''),
            ],
            'profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => trim($firstName.' '.$lastName),
                'matricule' => (string) ($profile?->matricule ?? ''),
                'agency_id' => $profile?->agencies_id,
            ],
            'agency' => [
                'id' => $agency?->id,
                'name' => (string) ($agency?->name ?? ''),
                'city' => (string) ($agency?->city ?? ''),
                'country' => (string) ($agency?->country ?? ''),
            ],
            'roles' => $this->user?->roles?->pluck('name')->values()->all() ?? [],
            'custom' => $this->custom,
        ];
    }
}
