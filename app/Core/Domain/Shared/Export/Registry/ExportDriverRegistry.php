<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Registry;

use App\Core\Domain\Shared\Export\Contracts\ExportDriverInterface;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use App\Core\Domain\Shared\Export\Exceptions\UnsupportedFormatException;

final class ExportDriverRegistry
{
    /** @var array<string, ExportDriverInterface> */
    private array $drivers = [];

    public function register(ExportDriverInterface $driver): void
    {
        $this->drivers[$driver->format()->value] = $driver;
    }

    public function for(ExportFormat $format): ExportDriverInterface
    {
        if (! isset($this->drivers[$format->value])) {
            throw UnsupportedFormatException::forFormat($format->value);
        }

        return $this->drivers[$format->value];
    }

    /**
     * @return array<int, string>
     */
    public function availableFormats(): array
    {
        return array_keys($this->drivers);
    }
}
