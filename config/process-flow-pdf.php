<?php

declare(strict_types=1);

use App\Core\Domain\Shared\Export\Support\PdfLayoutMetrics;

return [
    'paper' => env('PROCESS_FLOW_PDF_PAPER', 'a4'),
    'margins' => PdfLayoutMetrics::printMarginsMm(),
];
