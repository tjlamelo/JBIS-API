<?php

namespace App\Core\Domain\Identity\Support;

/**
 * Rôles applicatifs (Spatie) — source unique de vérité.
 */
final class ApplicationRole
{
    public const SUPERADMIN = 'superadmin';

    public const ADMIN = 'admin';

    public const STAFF = 'staff';

    public const PARTNER = 'partner';

    public const RECRUITER = 'recruiter';

    public const CANDIDATE = 'candidate';

    /** @var list<string> */
    public const STAFF_ROLES = [
        self::SUPERADMIN,
        self::ADMIN,
        self::STAFF,
    ];

    /** @var list<string> */
    public const ALL = [
        self::SUPERADMIN,
        self::ADMIN,
        self::STAFF,
        self::PARTNER,
        self::RECRUITER,
        self::CANDIDATE,
    ];

    public static function isStaff(string $role): bool
    {
        return in_array($role, self::STAFF_ROLES, true);
    }

    public static function isCandidate(string $role): bool
    {
        return $role === self::CANDIDATE;
    }

    public static function isRecruiter(string $role): bool
    {
        return $role === self::RECRUITER;
    }
}
