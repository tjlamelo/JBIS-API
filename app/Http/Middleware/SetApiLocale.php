<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    private const SUPPORTED_LOCALES = ['en', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $requestedLocale = $this->resolveLocale($request);
        $locale = in_array($requestedLocale, self::SUPPORTED_LOCALES, true)
            ? $requestedLocale
            : config('app.fallback_locale', 'en');

        App::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolveLocale(Request $request): ?string
    {
        $locale = $request->header('X-Locale')
            ?? $request->query('lang')
            ?? $request->getPreferredLanguage(self::SUPPORTED_LOCALES);

        if (! is_string($locale) || $locale === '') {
            return null;
        }

        return strtolower(substr($locale, 0, 2));
    }
}
