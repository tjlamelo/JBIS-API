<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Exceptions;

use RuntimeException;

final class ProfileLockedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('Ce profil est approuvé et ne peut plus être modifié.'));
    }
}
