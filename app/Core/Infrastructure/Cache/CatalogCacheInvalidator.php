<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Cache;

final class CatalogCacheInvalidator
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function invalidate(): void
    {
        $this->cache->bumpCatalog();
    }
}
