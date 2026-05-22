<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Registry;

use App\Core\Domain\Shared\Export\Contracts\ExportSourceInterface;
use App\Core\Domain\Shared\Export\Exceptions\UnknownSourceException;

/**
 * Registre extensible des sources d'export.
 *
 * Les sources sont enregistrées par le ServiceProvider, mais peuvent
 * aussi être ajoutées dynamiquement (ex. pour des plugins/modules).
 */
final class ExportSourceRegistry
{
    /** @var array<string, ExportSourceInterface> */
    private array $sources = [];

    public function register(ExportSourceInterface $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    public function has(string $key): bool
    {
        return isset($this->sources[$key]);
    }

    public function get(string $key): ExportSourceInterface
    {
        if (! isset($this->sources[$key])) {
            throw UnknownSourceException::forKey($key);
        }

        return $this->sources[$key];
    }

    /**
     * @return array<string, ExportSourceInterface>
     */
    public function all(): array
    {
        return $this->sources;
    }
}
