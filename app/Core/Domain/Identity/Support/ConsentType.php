<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

final class ConsentType
{
    public const TERMS = 'TERMS';

    public const PRIVACY = 'PRIVACY';

    public const COOKIES = 'COOKIES';

    public const MARKETING = 'MARKETING';

    /** @var list<string> */
    public const ALL = [
        self::TERMS,
        self::PRIVACY,
        self::COOKIES,
        self::MARKETING,
    ];
}
