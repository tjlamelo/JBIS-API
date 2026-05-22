<?php

declare(strict_types=1);

return [
    'url' => rtrim((string) env('SCREENSHOT_SERVICE_URL', 'http://127.0.0.1:3100'), '/'),
    'token' => (string) env('SCREENSHOT_SERVICE_TOKEN', ''),
    'timeout' => (int) env('SCREENSHOT_SERVICE_TIMEOUT', 120),
];
