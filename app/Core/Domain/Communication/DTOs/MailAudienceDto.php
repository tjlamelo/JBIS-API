<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class MailAudienceDto
{
    /**
     * @param array<int, int> $userIds
     * @param array<int, string> $roles
     * @param array<int, int> $agencyIds
     */
    public function __construct(
        public string $mode = 'all',
        public array $userIds = [],
        public array $roles = [],
        public array $agencyIds = [],
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

        $mode = (string) ($targeting['mode'] ?? 'all');

        return new self(
            mode: $mode,
            userIds: $userIds,
            roles: $roles,
            agencyIds: $agencyIds,
        );
    }
}
