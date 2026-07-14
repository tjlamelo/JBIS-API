<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePasswordIsChanged
{
    /** @var list<string> */
    private const ALLOWED_ROUTE_NAMES = [
        'api.me',
        'api.logout',
        'api.required-password-change',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->must_change_password) {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if (in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        return BaseResponse::forbidden([
            'message' => __('Vous devez changer votre mot de passe avant de continuer.'),
            'must_change_password' => true,
        ])->toJsonResponse();
    }
}
