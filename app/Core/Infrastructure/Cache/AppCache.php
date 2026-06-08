<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

final class AppCache
{
    private const CATALOG_VERSION_KEY = 'meta:catalog_version';

    private const REFERENCE_VERSION_KEY = 'meta:reference_version';

    public function catalogVersion(): int
    {
        return (int) Cache::get(self::CATALOG_VERSION_KEY, 1);
    }

    public function bumpCatalog(): void
    {
        if (! Cache::has(self::CATALOG_VERSION_KEY)) {
            Cache::forever(self::CATALOG_VERSION_KEY, 2);

            return;
        }

        Cache::increment(self::CATALOG_VERSION_KEY);
    }

    public function referenceVersion(): int
    {
        return (int) Cache::get(self::REFERENCE_VERSION_KEY, 1);
    }

    public function bumpReference(): void
    {
        if (! Cache::has(self::REFERENCE_VERSION_KEY)) {
            Cache::forever(self::REFERENCE_VERSION_KEY, 2);

            return;
        }

        Cache::increment(self::REFERENCE_VERSION_KEY);
    }

    public function catalogKey(string $segment, string $locale, mixed $suffix = null): string
    {
        $version = $this->catalogVersion();
        $hash = $this->hashSuffix($suffix);

        return "public:c{$version}:{$segment}:{$locale}".($hash !== '' ? ":{$hash}" : '');
    }

    public function referenceKey(string $segment, string $locale = '', mixed $suffix = null): string
    {
        $version = $this->referenceVersion();
        $hash = $this->hashSuffix($suffix);
        $localePart = $locale !== '' ? ":{$locale}" : '';

        return "ref:v{$version}:{$segment}{$localePart}".($hash !== '' ? ":{$hash}" : '');
    }

    public function dashboardKey(int $userId, string $locale): string
    {
        return "dash:payload:{$userId}:{$locale}";
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        return Cache::remember($key, $ttlSeconds, $callback);
    }

    private function hashSuffix(mixed $suffix): string
    {
        if ($suffix === null) {
            return '';
        }

        if (is_string($suffix)) {
            return $suffix;
        }

        return md5(json_encode($suffix) ?: '');
    }
}
