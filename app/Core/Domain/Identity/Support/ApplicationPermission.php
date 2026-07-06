<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

/**
 * Registre des ressources et noms de permissions (resource.action).
 */
final class ApplicationPermission
{
    public const VIEW = 'view';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    /** @var list<string> */
    public const ACTIONS = [self::VIEW, self::CREATE, self::UPDATE, self::DELETE];

    /** Permissions système (hors CRUD par ressource). */
    public const ADMIN_ACCESS = 'admin.access';

    public const PERMISSION_MANAGE = 'permission.manage';

    /** Guard Spatie unique (aligné seeders + rôles). */
    public const GUARD = 'web';

    /** @var list<string> */
    public const RESOURCES = [
        'agency',
        'application',
        'applicationdocument',
        'archive',
        'certification',
        'certificationoffer',
        'company',
        'education',
        'experience',
        'interestandhobby',
        'interview',
        'offer',
        'payment',
        'paymentinstallment',
        'paymentschedule',
        'processflow',
        'processstep',
        'professionalprofile',
        'program',
        'recruiterassignment',
        'recruiteronboarding',
        'recruiteroffer',
        'recruiterorganization',
        'rendezvous',
        'requireddocument',
        'training',
        'user',
        'userconsent',
        'userdevice',
        'userdocument',
        'userinternship',
        'userlanguage',
        'usernote',
        'userpreferredcountry',
        'userprofile',
        'usersettings',
        'userskill',
        'usertraining',
        'uservisahistory',
    ];

    public static function name(string $resource, string $action): string
    {
        return strtolower($resource).'.'.strtolower($action);
    }

    /**
     * @return list<string>
     */
    public static function forResource(string $resource): array
    {
        return array_map(
            fn (string $action): string => self::name($resource, $action),
            self::ACTIONS,
        );
    }

    /**
     * @return list<string>
     */
    public static function allCrudNames(): array
    {
        $names = [];
        foreach (self::RESOURCES as $resource) {
            array_push($names, ...self::forResource($resource));
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    public static function systemNames(): array
    {
        return [self::ADMIN_ACCESS, self::PERMISSION_MANAGE];
    }

    /**
     * @return list<string>
     */
    public static function allNames(): array
    {
        return [...self::allCrudNames(), ...self::systemNames()];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function groupedByResource(): array
    {
        $groups = [];
        foreach (self::RESOURCES as $resource) {
            $groups[$resource] = self::forResource($resource);
        }

        $groups['_system'] = self::systemNames();

        return $groups;
    }
}
