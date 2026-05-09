<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class SmsAudienceDto
{
    /**
     * @param array<int, int> $userIds
     * @param array<int, string> $roles
     * @param array<int, int> $agencyIds
     * @param array<int, string> $manualNumbers
     */
    public function __construct(
        public string $mode = 'all',
        public array $userIds = [],
        public array $roles = [],
        public array $agencyIds = [],
        public array $manualNumbers = [],
    ) {}

    /**
     * @param array<string, mixed> $targeting
     */
    public static function fromArray(array $targeting): self
    {
        $userIds = collect($targeting['user_ids'] ?? [])
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $roles = collect($targeting['roles'] ?? [])
            ->filter(fn (mixed $role): bool => is_string($role) && $role !== '')
            ->map(fn (string $role): string => trim($role))
            ->values()
            ->all();

        $agencyIds = collect($targeting['agency_ids'] ?? [])
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $manualNumbers = collect($targeting['manual_numbers'] ?? [])
            ->filter(fn (mixed $number): bool => is_string($number) && $number !== '')
            ->map(fn (string $number): string => trim($number))
            ->values()
            ->all();

        return new self(
            mode: (string) ($targeting['mode'] ?? 'all'),
            userIds: $userIds,
            roles: $roles,
            agencyIds: $agencyIds,
            manualNumbers: $manualNumbers,
        );
    }
}
