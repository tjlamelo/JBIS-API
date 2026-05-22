<?php

declare(strict_types=1);

return [
    'paper' => env('PROCESS_FLOW_PDF_PAPER', 'a4'),
    'margins' => [
        'top' => 12,
        'right' => 12,
        'bottom' => 14,
        'left' => 12,
    ],
];
