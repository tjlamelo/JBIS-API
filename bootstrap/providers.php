<?php

use App\Core\Domain\Identity\Providers\Document\DocumentServiceProvider;
use App\Core\Domain\Shared\Ai\Providers\AiServiceProvider;
use App\Core\Domain\Shared\Export\Providers\ExportServiceProvider;
use App\Core\Domain\Shared\Pdf\Providers\PdfServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    FortifyServiceProvider::class,
    DocumentServiceProvider::class,
    AiServiceProvider::class,
    ExportServiceProvider::class,
    PdfServiceProvider::class,
];
