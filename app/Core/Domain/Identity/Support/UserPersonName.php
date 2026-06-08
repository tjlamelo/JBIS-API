<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Identity\Models\User;

/**
 * Prénom / nom : stockés sur user_profiles, avec repli sur users.name.
 */
final class UserPersonName
{
    public const USER_COLUMNS = 'id,name,email';

    public const PROFILE_COLUMNS = 'user_id,first_name,last_name';

    /**
     * @return list<string>
     */
    public static function withProfile(string $relation): array
    {
        return [
            "{$relation}:".self::USER_COLUMNS,
            "{$relation}.profile:".self::PROFILE_COLUMNS,
        ];
    }

    public static function firstName(User $user): string
    {
        $fromProfile = trim((string) ($user->profile?->first_name ?? ''));

        if ($fromProfile !== '') {
            return $fromProfile;
        }

        return self::splitFullName((string) ($user->name ?? ''))[0];
    }

    public static function lastName(User $user): string
    {
        $fromProfile = trim((string) ($user->profile?->last_name ?? ''));

        if ($fromProfile !== '') {
            return $fromProfile;
        }

        return self::splitFullName((string) ($user->name ?? ''))[1];
    }

    /**
     * @return array{id: int, first_name: string, last_name: string, email: string}
     */
    public static function toContactArray(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => self::firstName($user),
            'last_name' => self::lastName($user),
            'email' => (string) $user->email,
        ];
    }

    /**
     * @return array{id: int, first_name: string, last_name: string}
     */
    public static function toActorArray(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => self::firstName($user),
            'last_name' => self::lastName($user),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $first = array_shift($parts) ?? '';

        return [$first, trim(implode(' ', $parts))];
    }
}
