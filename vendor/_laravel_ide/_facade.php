<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \App\Core\Domain\Identity\Models\User|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \App\Core\Domain\Identity\Models\User|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \App\Core\Domain\Identity\Models\User|null
     */
    public static function getUser();

    /**
     * @return \App\Core\Domain\Identity\Models\User
     */
    public static function authenticate();

    /**
     * @return \App\Core\Domain\Identity\Models\User|null
     */
    public static function user();

    /**
     * @return \App\Core\Domain\Identity\Models\User|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \App\Core\Domain\Identity\Models\User
     */
    public static function getLastAttempted();
}