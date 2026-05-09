<?php

namespace App\Core\Domain\Identity\DTOs;

final readonly class DeviceContextDto
{
    public function __construct(
        public string $ip,
        public string $userAgent,
        public string $deviceName = 'api',
        public string $secChUa = '',
        public string $secChUaPlatform = '',
        public string $acceptLanguage = '',
    ) {}
}
