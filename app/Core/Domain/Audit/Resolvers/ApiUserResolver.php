<?php

declare(strict_types=1);

namespace App\Core\Domain\Audit\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\UserResolver;

final class ApiUserResolver implements UserResolver
{
    public static function resolve(): ?Authenticatable
    {
        /** Requêtes API Sanctum : l'utilisateur est sur la requête, pas toujours sur Auth::user(). */
        $requestUser = Request::user();
        if ($requestUser !== null) {
            return $requestUser;
        }

        $user = Auth::user();
        if ($user !== null) {
            return $user;
        }

        foreach (Config::get('audit.user.guards', ['sanctum', 'web']) as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    return Auth::guard($guard)->user();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
