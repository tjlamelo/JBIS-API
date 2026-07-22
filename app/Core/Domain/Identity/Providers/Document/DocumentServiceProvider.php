<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Providers\Document;

use App\Core\Domain\Identity\Actions\Document\DownloadUserDocumentAction;
use App\Core\Domain\Identity\Actions\Document\DownloadUserDocumentsZipAction;
use App\Core\Domain\Identity\Services\Document\CvSourceTextPreparer;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;
use App\Core\Domain\Identity\Services\Document\DocumentTypeResolver;
use App\Core\Domain\Identity\Services\Document\GoogleCloudVisionOcrService;
use App\Core\Domain\Identity\Support\Document\DocumentDownloadNameBuilder;
use Illuminate\Support\ServiceProvider;

final class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DocumentTypeResolver::class);
        $this->app->singleton(GoogleCloudVisionOcrService::class);
        $this->app->singleton(CvSourceTextPreparer::class);
        $this->app->singleton(DocumentStorageService::class);
        $this->app->singleton(DocumentDownloadNameBuilder::class);
        $this->app->singleton(DownloadUserDocumentAction::class);
        $this->app->singleton(DownloadUserDocumentsZipAction::class);
    }
}
